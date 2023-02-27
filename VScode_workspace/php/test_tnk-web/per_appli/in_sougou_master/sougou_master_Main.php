<?php
////////////////////////////////////////////////////////////////////////////////
// 総合届（マスター）メイン部                                                 //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_master_Main.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
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
    $menu->set_title('総合届（承認 経路 マスター）');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
//    $menu->set_RetUrl($_SESSION['product_master_referer']);             // 通常は指定する必要はない
    if($request->get('showMenu')!='2'){
        $request->add('showMenu', '1');
    }

    if($request->get('showMenu')=='1') {
        get_serch_master($result, $request);                          // 各種データの取得
        
        request_check($request, $result, $menu);           // 処理の分岐チェック
    } else {
        get_serch_master2($result, $request);                          // 各種データの取得
        
        request_check2($request, $result, $menu);           // 処理の分岐チェック
    }
    
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
    require_once ('sougou_master_View.php');

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
        $request->add('act_id', '');
        $request->add('kakarityo', '');
        $request->add('katyo', '');
        $request->add('butyo', '');

        get_serch_master($result, $request);    // 各種データの取得
        if( $result->get('rows_g2') != '' ){
            $res_g2 = $result->get_array2('res_g2');
            $standards_date = $res_g2[0][0];
            $somukatyo = $res_g2[0][1];
            $kanributyo = $res_g2[0][2];
            $kojyotyo = $res_g2[0][3];
            $request->add('standards_date', $standards_date);
            $request->add('somukatyo', $somukatyo);
            $request->add('kanributyo', $kanributyo);
            $request->add('kojyotyo', $kojyotyo);
        }
    }
}

////////////// 処理の分岐を行う
function request_check2($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = productMaster_copy2($request, $result);
    if ($request->get('del') != '') $ok = productMaster_del2($request);
    if ($request->get('entry') != '')  $ok = productMaster_entry2($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');

        $request->add('standards_date', '');
        $request->add('somukatyo', '');
        $request->add('kanributyo', '');
        $request->add('kojyotyo', '');

        get_serch_master2($result, $request);    // 各種データの取得
    }
}

// 社員番号をチェック
function checkUid($uid, &$code)
{
    if( $uid == '' ) {
        $code = '------';
        return true;
    }

    if( !is_numeric($uid) ) {
        $_SESSION['s_sysmsg'] .= "$uid : 登録できない社員番号です。";
        return false;
    }

    $query = sprintf("SELECT retire_date FROM user_detailes WHERE uid='%s'", $uid);
    $res = array();
    if( getResultWithField2($query, $field, $res) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "$uid : データベース検索に失敗しました。";
        return false;
    }

    if( $res[0][0] != '' ) {
        $_SESSION['s_sysmsg'] .= "$uid : 登録できない社員番号です。";
        return false;
    }

    $code = $uid;

    return true;
}

// チェックボックスのフラグチェック
function checkOnOff($flag)
{
    if( $flag == '' ) {
        return '------';
    }

    return $flag;
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function productMaster_entry($request, $result)
{

    if (getCheckAuthority(22)) {                    // 認証チェック
        $act_id = $request->get('act_id');
        if( $act_id == '' ) return false;

        if( !checkUid($request->get('kakarityo'), $kakarityo) ) return false;
        if( !checkUid($request->get('katyo'), $katyo) ) return false;
        if( !checkUid($request->get('butyo'), $butyo) ) return false;

        $somukatyo = checkOnOff($request->get('somukatyo'));
        $kanributyo = checkOnOff($request->get('kanributyo'));
        $kojyotyo = checkOnOff($request->get('kojyotyo'));

        $query = sprintf("SELECT act_id FROM approval_path_master WHERE act_id='%s'", $act_id);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE approval_path_master SET act_id='%s', kakarityo='%s', katyo='%s', butyo='%s', somukatyo='%s', kanributyo='%s', kojyotyo='%s' WHERE act_id='%s'", $act_id, $kakarityo, $katyo, $butyo, $somukatyo, $kanributyo, $kojyotyo, $act_id);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 変更に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO approval_path_master (act_id, kakarityo, katyo, butyo, somukatyo, kanributyo, kojyotyo)
                              VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s' )",
                                $act_id, $kakarityo, $katyo, $butyo, $somukatyo, $kanributyo, $kojyotyo);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 追加しました！";    // .= に注意
                return true;
            }
        }
    } else {                                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function productMaster_entry2($request, $result)
{

    if (getCheckAuthority(22)) {                    // 認証チェック
        $standards_date = $request->get('standards_date');

        if( !checkUid($request->get('somukatyo'), $somukatyo) ) return false;
        if( !checkUid($request->get('kanributyo'), $kanributyo) ) return false;
        if( !checkUid($request->get('kojyotyo'), $kojyotyo) ) return false;

        $query = sprintf("SELECT standards_date FROM approval_path_master_Late WHERE standards_date='%s'", $standards_date);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE approval_path_master_Late SET standards_date='%s', somukatyo='%s', kanributyo='%s', kojyotyo='%s' WHERE standards_date='%s'", $standards_date, $somukatyo, $kanributyo, $kojyotyo, $standards_date);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター 変更に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター 変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO approval_path_master_Late (standards_date, somukatyo, kanributyo, kojyotyo)
                              VALUES ('%s', '%s', '%s', '%s' )",
                                $standards_date, $somukatyo, $kanributyo, $kojyotyo);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター 追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター 追加しました！";    // .= に注意
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
        $act_id = $request->get('act_id');
        $query = sprintf("DELETE FROM approval_path_master WHERE act_id = '%s'", $act_id);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "経理コード：{$act_id} 承認 経路 削除しました！"; // .= に注意
            return true;
        }
    } else {                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function productMaster_del2($request)
{

    if (getCheckAuthority(22)) {    // 認証チェック
        $standards_date = $request->get('standards_date');
        $query = sprintf("DELETE FROM approval_path_master_Late WHERE standards_date = '%s'", $standards_date);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "基準日：{$standards_date} マスター削除しました！"; // .= に注意
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
        SELECT
            act_id          AS 経理コード
            ,kakarityo           AS 所属係長
            ,katyo               AS 所属課長
            ,butyo               AS 所属部長
            ,somukatyo           AS 総務課長
            ,kanributyo          AS 管理部長
            ,kojyotyo            AS 工場長
        FROM
            approval_path_master
        ORDER BY
            act_id
    ";
    $res_g = array();

    $query_g2 = "
        SELECT
            standards_date      AS 基準日
            ,somukatyo           AS 総務課長
            ,kanributyo          AS 管理部長
            ,kojyotyo            AS 工場長
        FROM
            approval_path_master_Late
        WHERE
            standards_date <= CURRENT_TIMESTAMP
        ORDER BY
            standards_date DESC
        LIMIT 1
    ";
    $res_g2 = array();

    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0
        ||($rows_g2 = getResultWithField2($query_g2, $field_g2, $res_g2)) <= 0) {
        $_SESSION['s_sysmsg'] = "承認 経路 の登録がありません！";
        $result->add_array2('res_g', '');
        $field = array("経理コード","所属係長","所属課長","所属部長","総務課長","管理部長","工場長");
        $result->add_array2('field_g', $field);

        $result->add('num_g', 7);
        $result->add('rows_g', '');
        $result->add_array2('res_g2', '');
        $result->add_array2('field_g2', '');
        $result->add('num_g2', 0);
        $result->add('rows_g2', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
        $num_g2 = count($field_g2);
        $result->add_array2('res_g2', $res_g2);
        $result->add_array2('field_g2', $field_g2);
        $result->add('num_g2', $num_g2);
        $result->add('rows_g2', $rows_g2);
    }
}

////////////// 表示用(一覧表)の総務課長～工場長マスターデータをSQLで取得
function get_serch_master2($result, $request)
{
    $query_g = "
        SELECT
            standards_date      AS 基準日
            ,somukatyo           AS 総務課長
            ,kanributyo          AS 管理部長
            ,kojyotyo            AS 工場長
        FROM
            approval_path_master_Late
        ORDER BY
            standards_date DESC
    ";
    $res_g = array();

    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "総務課長～工場長 の登録がありません！";
        $result->add_array2('res_g', '');
        $field = array("基準日","総務課長","管理部長","工場長");
        $result->add_array2('field_g', $field);

        $result->add('num_g', 4);
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
    $max = $result->get('num_g');

    for( $i=0; $i<$max; $i++ ) {
        if( strcmp($res_g[$r][$i], '------') == 0 ) $res_g[$r][$i] = '';
    }

    $act_id   = $res_g[$r][0];
    $kakarityo = $res_g[$r][1];
    $katyo = $res_g[$r][2];
    $butyo = $res_g[$r][3];

    $somukatyo = trim($res_g[$r][4]);
    $kanributyo = trim($res_g[$r][5]);
    $kojyotyo = trim($res_g[$r][6]);

    $request->add('act_id', $act_id);
    $request->add('kakarityo', $kakarityo);
    $request->add('katyo', $katyo);
    $request->add('butyo', $butyo);
    $request->add('somukatyo', $somukatyo);
    $request->add('kanributyo', $kanributyo);
    $request->add('kojyotyo', $kojyotyo);

}

////////////// コピーのリンクが押された時
function productMaster_copy2($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $max = $result->get('num_g');

    for( $i=0; $i<$max; $i++ ) {
        if( strcmp($res_g[$r][$i], '------') == 0 ) $res_g[$r][$i] = '';
    }

    $standards_date = $res_g[$r][0];
    $somukatyo = trim($res_g[$r][1]);
    $kanributyo = trim($res_g[$r][2]);
    $kojyotyo = trim($res_g[$r][3]);

    $request->add('standards_date', $standards_date);
    $request->add('somukatyo', $somukatyo);
    $request->add('kanributyo', $kanributyo);
    $request->add('kojyotyo', $kojyotyo);
}

////////////// 照会用グループコード照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='sougou_master_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>\n";
    if($request->get('showMenu')=='1') {
        $listTable .= "                総合届 承認 経路 マスター\n";
    } else {
        $listTable .= "                総務課長～工場長 マスター\n";
    }
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
        $listTable .= "                <a href='../sougou_master_Main.php?number=". $r . "&showMenu=" . $request->get('showMenu') . "' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        $res_g2 = $result->get_array2('res_g2');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:
                case 1:
                case 2:
                case 3:
                    if( empty($res_g[$r][$i]) ) {
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". '------' ."</div></td>\n";
                    } else {
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                    }
                    break;
                default:
                    if( strcmp($res_g[$r][$i], '------') == 0 || empty($res_g[$r][$i]) ) {
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". '------' ."</div></td>\n";
                    } else {
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". "〇" ."</div></td>\n";
                    }
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
if($request->get('showMenu')=='1') {
    $file_name = "list/sougou_master_List-{$_SESSION['User_ID']}.html";
} else {
    $file_name = "list/sougou_master_List2-{$_SESSION['User_ID']}.html";
}
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
