<?php
//////////////////////////////////////////////////////////////////////////////
// 就業週報の集計 修正内容の入力                                   Main 部  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/11/21 Created   working_hours_report_CorrectMain.php                //
// 2017/06/02 部課長説明 本格稼動                                           //
// 2017/06/29 エラー箇所等を訂正                                            //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);

require_once ('../../MenuHeader.php');               // TNK 全共通 menu class
require_once ('../../tnk_func.php');                 // day_off(), date_offset() で使用
require_once ('../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../function.php');                 // access_log()等で使用

Correctmain();

function Correctmain()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('訂正内容の入力');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
    
    $request = new Request;
    $result  = new Result;
    
    getCorrectData($result, $request);                          // 各種データの取得
    
    requestCheck($request, $result, $menu);           // 処理の分岐チェック
    
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
    require_once ('working_hours_report_CorrectView.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function requestCheck($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = correctCopy($request, $result);
    if ($request->get('del') != '') $ok = correctDel($request);
    if ($request->get('entry') != '')  $ok = correctEntry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        getCorrectData($result, $request);    // 各種データの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function correctEntry($request, $result)
{
    $uid = $request->get('uid');
    $uid = sprintf('%06d', $uid);
    $working_date = $request->get('working_date');
    $correct_contents = $request->get('correct_contents');
    $query = sprintf("SELECT * FROM working_hours_report_correct WHERE uid='%s' AND working_date=%d", $uid, $working_date);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
        $query = sprintf("UPDATE working_hours_report_correct SET correct_contents='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $correct_contents, $_SESSION['User_ID'], $uid, $working_date);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}の訂正内容変更失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}の訂正内容を変更しました！"; // .= に注意
            return true;
        }
    } else {                                    // 登録なし INSERT 新規   
        $query = sprintf("INSERT INTO working_hours_report_correct (uid, working_date, correct_contents, correct, last_date, last_user)
                          VALUES ('%s', '%s', '%s', FALSE, CURRENT_TIMESTAMP, '%s')",
                            $uid, $working_date, $correct_contents, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}の訂正の追加に失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "社員番号：{$uid}  就業年月日：{$working_date}の訂正を追加しました！";    // .= に注意
            return true;
        }
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function correctDel($request)
{
    
    $uid = $request->get('uid');
    $working_date = $request->get('working_date');
    $query = sprintf("DELETE FROM working_hours_report_correct WHERE uid='%s' AND working_date='%s'", $uid, $working_date);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "社員番号：{$uid} 就業年月日：{$working_date}の訂正内容の削除に失敗！";   // .= に注意
        $msg_flg = 'alert';
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "社員番号：{$uid} 就業年月日：{$working_date}の訂正内容を削除しました！"; // .= に注意
        return true;
    }
}
////////////// 表示用(一覧表)の就業週報訂正データをSQLで取得
function getCorrectData ($result, $request)
{
    $query_g = "
        SELECT  uid                AS 社員番号     -- 0
            ,   working_date       AS 就業年月日   -- 1
            ,   correct_contents   AS 訂正内容     -- 2
        FROM
            working_hours_report_correct
        WHERE last_user = {$_SESSION['User_ID']} AND correct = 'f'
        ORDER BY
            uid
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $field_g[0]   = "社員番号";
        $field_g[1]   = "社員名";
        $field_g[2]   = "就業年月日";
        $field_g[3]   = "訂正内容";
        $num_g = count($field_g);
        $num_g = $num_g + 1;
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', '');
        $result->add('get_flg', 't');
    } else {
        $num_g = count($field_g);
        for ($i=0; $i<$rows_g; $i++) {
            $user_name[$i] = getViewUserName($res_g[$i][0]);
        }
        $result->add_array2('user_name', $user_name);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
        $result->add('get_flg', '');
    }
}

function getViewUserName($uid)
{
    $query_n = "
        SELECT trim(name) AS 氏名
        FROM
            user_detailes
        WHERE
            uid = '{$uid}'
        
    ";
    $res_n = array();
    if ( ($rows_n=getResult2($query_n, $res_n)) < 1 ) {
        $user_name = '未登録';
    } else {
        $user_name = $res_n[0][0];
    }
    
    return $user_name;
    
}

////////////// コピーのリンクが押された時
function correctCopy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $uid   = $res_g[$r][0];
    $working_date = $res_g[$r][1];
    $correct_contents = $res_g[$r][2];
    $request->add('uid', $uid);
    $request->add('working_date', $working_date);
    $request->add('correct_contents', $correct_contents);
}

////////////// 就業週報訂正入力画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='/base_class.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../working_hours_report.css' type='text/css' media='screen'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "body {\n";
    $listTable .= "    background-image:none;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "<script type='text/javascript' src='../working_hours_report.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='working_hours_report_CorrectMain.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field_g = $result->get_array2('field_g');
    $listTable .= "                <th class='winbox' nowrap>社員番号</th>\n";
    $listTable .= "                <th class='winbox' nowrap>社員名</th>\n";
    $listTable .= "                <th class='winbox' nowrap>就業年月日</th>\n";
    $listTable .= "                <th class='winbox' nowrap>訂正内容</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    if ($result->get('get_flg') == 't') {
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' colspan='5' nowrap align='center'><div class='pt9'>訂正内容の登録がありません</div></td>\n";
        $listTable .= "    </tr>\n";
    } else {
        for ($r=0; $r<$result->get('rows_g'); $r++) {
            $listTable .= "        <tr>\n";
            $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
            $listTable .= "                <a href='../working_hours_report_CorrectMain.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
            $cnum = $r + 1;
            $listTable .= "                ". $cnum ."\n";
            $listTable .= "                </a>\n";
            $listTable .= "            </td>\n";
            $res_g     = $result->get_array2('res_g');
            $user_name = $result->get_array2('user_name');
            for ($i=0; $i<$result->get('num_g'); $i++) {    // レコード数分繰返し
                switch ($i) {
                    case 0:                                 // 社員番号
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $user_name[$r] ."</div></td>\n";
                    break;
                    case 1:                                 // 就業年月日
                        $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                    break;
                    case 2:                                 // 訂正内容
                        $listTable .= "<td class='winbox' nowrap align='left' width='700'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                    break;
                    default:
                    break;
                }
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

////////////// 就業週報訂正入力画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    ////////// HTMLファイル出力
    $file_name = "list/working_hours_report_Correct_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
