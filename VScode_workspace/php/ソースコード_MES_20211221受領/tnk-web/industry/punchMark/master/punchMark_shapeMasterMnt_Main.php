<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 形状マスター メンテナンス メイン部                              //
// Copyright (C) 2007 Norihisa.Ooya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2007/07/24 Created   punchMark_shapeMasterMnt_Main.php                   //
// 2007/09/26 site_index を INDEX_INDUST へ変更  小林                       //
// 2007/10/20 getViewHTMLbody()<body>を追加 E_ALL → E_ALL | E_STRICTへ 小林//
//            リストのヘッダーを<iframe>で追加 小林                         //
// 2007/10/24 プログラムの最後に改行を追加                                  //
// 2007/11/09 getPreDataRows()とsetEditHistory()編集履歴を追加。$menuの抜け //
//              request_check($request, $result, $menu)以下３箇所       小林//
// 2007/11/10 putErrorLogWrite()を使いしてSQLエラーのdebugを行う        小林//
//            余分な<font color='yellow'></font>を削除                  小林//
// 2008/04/18 購買依頼により形状の種類に'R'を追加                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
require_once ('punchMark_MasterFunction.php');      // 刻印管理システム共通マスター関数
access_log();                               // Script Name は自動取得

main();

function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    
    ////////////// サイト設定
    $menu->set_site(INDEX_INDUST, 999);         // site_index=生産メニュー site_id=999(サイトメニューを開く)
    
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('刻印管理 形状マスター メンテナンス');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result);              // 削除チェック用マスターデータの取得
    
    branch_check($request, $result, $menu);     // 処理の分岐チェック
    
    get_data($request, $result);    // 形状マスターデータ取得
    
    outViewListHTML($request, $menu, $result);  // View用HTMLファイルの出力
    
    display($menu, $request, $result);          // 画面表示
}

// 画面表示
function display($menu, $request, $result)
{       
    //////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');

    ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
    
    /////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
 
    /////////// Viewの処理
    require_once ('punchMark_shapeMasterMnt_View.php');

    ob_end_flush(); 
}

function branch_check($request, $result, $menu)    //処理の分岐を行う
{
    if ($request->get('entry') != '') punchMark_shape_entry($request, $menu);
    if ($request->get('del') != '') punchMark_shape_del($request, $result, $menu);
    $request->add('shape_code', '');
    $request->add('shape_name', '');
    $request->add('note', '');
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// 登録・変更ロジック
function punchMark_shape_entry($request, $menu)
{
    $shape_code = $request->get('shape_code');
    $shape_name = $request->get('shape_name');
    $note      = $request->get('note');
    $query = sprintf("SELECT * FROM punchMark_shape_master WHERE shape_code=%d", $shape_code);
    $old_data=getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 更新
        $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_code=%d AND shape_name='%s'", $shape_code, $shape_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {   //////// コード・形状名変更なし UPDATE 備考の変更
            $query = sprintf("UPDATE punchMark_shape_master SET shape_code=%d, shape_name='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE shape_code=%d", $shape_code, $shape_name, $note, $_SESSION['User_ID'], $shape_code);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}の変更失敗！";    // .= に注意
                putErrorLogWrite($query);
                header('Location: ' . H_WEB_HOST . $menu->out_self());
                exit();
            } else {
                $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code}形状名：{$shape_name}に変更しました！";
                // 編集履歴保存
                setEditHistory('punchMark_shape_master', 'U', $query, $old_data);
            }
        } else {    //形状名変更あり 形状名がすでに登録されているかチェック
            $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_name='%s'", $shape_name);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 名前登録あり エラー
                $_SESSION['s_sysmsg'] .= "形状名：{$shape_name}はすでに登録されています！！";    // .= に注意
                header('Location: ' . H_WEB_HOST . $menu->out_self());
                exit();
            } else {
                $query = sprintf("UPDATE punchMark_shape_master SET shape_code=%d, shape_name='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE shape_code=%d", $shape_code, $shape_name, $note, $_SESSION['User_ID'], $shape_code);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}の変更失敗！";    // .= に注意
                    putErrorLogWrite($query);
                    header('Location: ' . H_WEB_HOST . $menu->out_self());
                    exit();
                } else {
                    $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code}形状名：{$shape_name}に変更しました！";
                    // 編集履歴保存
                    setEditHistory('punchMark_shape_master', 'U', $query, $old_data);
                }
            }
        }
    } else {                                    //////// 登録なし INSERT 新規
        $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_name='%s'", $shape_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {   //////// 名前登録あり エラー
            $_SESSION['s_sysmsg'] .= "形状名：{$shape_name}はすでに登録されています！！";    // .= に注意
            header('Location: ' . H_WEB_HOST . $menu->out_self());
            exit();
        } else {
            $query = sprintf("INSERT INTO punchMark_shape_master (shape_code, shape_name, note, last_date, last_user)
                              VALUES (%d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $shape_code, $shape_name, $note, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}の追加に失敗！";    // .= に注意
                putErrorLogWrite($query);
            } else {
                $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}を追加しました！";
                // 編集履歴保存
                setEditHistory('punchMark_shape_master', 'I', $query);
            }
        }
    }
}

//////////// 削除ボタンが押された時
function punchMark_shape_del($request, $result, $menu)
{
    $shape_code = $request->get('shape_code');
    $shape_name = $request->get('shape_name');
    $res_punch = $result->get_array2('res_punch');
    $rows_punch  = $request->get('rows_punch');
    for ($r=0; $r<$rows_punch; $r++) {
        if ( $res_punch[$r][3] == $shape_code) {    // 刻印マスターで登録されているかチェック
            $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code}は刻印マスターで使用されています！！";
            header('Location: ' . H_WEB_HOST . $menu->out_self());
            exit();
        }
    }
    $query = "SELECT * FROM punchMark_shape_master WHERE shape_code = {$shape_code}";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '対象データの取得に失敗！ 管理担当者へ連絡して下さい。';
        header('Location: ' . H_WEB_HOST . $menu->out_self());
        exit();
    }
    $query = sprintf("DELETE FROM punchMark_shape_master WHERE shape_code = %d", $shape_code);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}の削除に失敗！";    // .= に注意
        putErrorLogWrite($query);
    } else {
        $_SESSION['s_sysmsg'] .= "形状コード：{$shape_code} 形状名：{$shape_name}を削除しました！";
        // 編集履歴保存
        setEditHistory('punchMark_shape_master', 'D', $query, $old_data);
    }
}

////////////// コピーのリンクが押された時  &&を追加 Undefined index対応
function pre_copy($request, $result)
{
    $res = array();
    $shape_code = $request->get('number');
    $request->add('shape_code', $shape_code);
    $query = "SELECT * FROM punchMark_shape_master WHERE shape_code=$shape_code";
    if (getResult($query, $res) <= 0) putErrorLogWrite($query);
    $shape_name = $res[0]['shape_name'];
    $note      = $res[0]['note'];
    $request->add('shape_name', $shape_name);
    $request->add('note', $note);
}

////////////// 表示用(一覧表)のマスターデータをSQLで取得
function get_data($request, $result)
{
    $query = "
        SELECT  shapem.shape_code               AS 形状コード     -- 0
            ,   shapem.shape_name               AS 形状名         -- 1
            ,   shapem.note                     AS 備考           -- 2
        FROM
            punchMark_shape_master AS shapem
        ORDER BY
            shape_code
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "形状コード";
        $field[1]   = "形状名";
        $field[2]   = "備考";
        $num = 3;
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    } else {
        $num = count($field);
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    }
}

////////////// 削除チェック用のマスターデータをSQLで取得
function get_master($request, $result)
{
    /////////初期設定
    if ($_SESSION['Auth'] >= 3) {
        $shape_name_master = array('丸', '四角', '板', 'ローラー', 'R', 'テスト'); //登録用形状名
        $request->add('rows_name', 6); //形状名の個数 ※形状名を追加するときは以上２つを変更する。
    } else {
        $shape_name_master = array('丸', '四角', '板', 'ローラー', 'R'); //登録用形状名
        $request->add('rows_name', 5); //形状名の個数 ※形状名を追加するときは以上２つを変更する。
    }
    $result->add_array2('shape_name_master', $shape_name_master);
    
    $query = "
        SELECT  punchm.punchMark_code           AS 刻印コード     -- 0
            ,   punchm.shelf_no                 AS 棚番           -- 1
            ,   punchm.mark                     AS 刻印内容       -- 2
            ,   punchm.shape_code               AS 形状コード     -- 3
            ,   punchm.size_code                AS サイズコード   -- 4
            ,   punchm.user_code                AS 客先コード     -- 5
            ,   punchm.note                     AS 備考           -- 6
            ,   punchm.make_flg                 AS 作成中フラグ   -- 7
        FROM
            punchMark_master as punchm
        ORDER BY
            punchMark_code
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add_array2('res_punch', '');
        $request->add('rows_punch', '');
    } else {
        $result->add_array2('res_punch', $res);
        $request->add('rows_punch', $rows);
    }
}

// View用HTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $res   = $result->get_array();
    $field = $result->get_array2('field');
    $num   = $request->get('num');
    $rows  = $request->get('rows');
    // 初期化
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../punchMark_MasterMnt.css' type='text/css' media='screen'>\n";
    $listTable .= "<script type='text/javascript' src='../punchMark_shapeMasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none; background-color:#d6d3ce;'>\n";
    $listTable .= "<center>\n";
    // $listTable .= "    <form name='entry_form' action='../punchMark_shapeMasterMnt_Main.php' method='post'>\n";
    $listTable .= "        <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    /*****
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td class='winbox_title' align='center' colspan='4'>\n";
    $listTable .= "                形状マスター\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox pt11b' nowrap >No</th>        <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
            $listTable .= "        <th class='winbox pt11b' nowrap>{$field[$i]}</th>\n";
        }
    $listTable .= "            </tr>\n";
    *****/
    for ($r=0; $r<$rows; $r++) {
        $listTable .= "<tr>\n";
        $listTable .= "    <td class='winbox' width='10%' align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        $listTable .= "        <a href='../punchMark_shapeMasterMnt_Main.php?copy_flg=1&number={$res[$r][0]}' target='_parent' style='text-decoration:none;'>\n";
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
            // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
            switch ($i) {
            case 0:     // サイズコード
                $listTable .= "<td class='winbox pt12b' width='20%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 1:     // サイズ名
                $listTable .= "<td class='winbox pt12b' width='20%' align='left'>{$res[$r][$i]}</td>\n";
                break;
            case 2:     // 備考
                if ($res[$r][$i] == '') {
                    $listTable .= "<td class='winbox pt12b' width='50%' align='left'>&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='50%' align='left'>{$res[$r][$i]}</td>\n";
                }
                break;
            default:
                break;
            }
        }
        $listTable .= "</tr>\n";
    }
    $listTable .= "</table>\n";
    $listTable .= "</td></tr>\n";
    $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
    // $listTable .= "</form>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

// View用HTMLの出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    // HTMLファイル出力
    $file_name = "list/punchMark_shapeMasterMnt_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
?>
