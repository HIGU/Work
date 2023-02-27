<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 部品番号マスター メンテナンス メイン部                          //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/07/30 Created   punchMark_partsMasterMnt_Main.php                   //
// 2007/09/25 header('Location: $menu->RetUrl()') →                        //
//            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl())  小林 //
// 2007/09/26 site_index を INDEX_INDUST へ変更  小林                       //
// 2007/10/02 リストに部品名を表示するよう追加                              //
//            部品番号の重複チェックを外して同じ部品番号で複数の刻印コードを//
//            登録できるように変更                                          //
// 2007/10/20 getViewHTMLbody()<body>を追加 E_ALL → E_ALL | E_STRICTへ     //
//            request_check()でエラーチェックしてエラーの場合データを残す   //
//            追加・変更・削除ロジックで成功・失敗のデータを返す            //
//            <a リンクの備考にurlencode()関数を使用 検索機能追加      小林 //
//            get_master()の刻印マスター取得に LIMIT 1 を追加(チェックのみ) //
// 2007/10/21 getSearchCondition()whereのnote条件を partsm.note へ変更 小林 //
// 2007/10/24 プログラムの最後に改行を追加                                  //
// 2007/11/10 getPreDataRows()とsetEditHistory()編集履歴を追加。        小林//
//            putErrorLogWrite()を使いしてSQLエラーのdebugを行う        小林//
//            Markを追加し行マーカーとジャンプをする                    小林//
// 2007/11/15 miitemをget_date()で取得。部品が同じ物は表示しない        小林//
// 2008/09/03 貸し出し中の場合は備考欄に表示するように変更                  //
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
    $menu->set_title('刻印管理 部品マスター メンテナンス');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result, $menu);       // 刻印マスター登録チェック用データ取得
    
    request_check($request, $result);           // 処理のリクエストチェック
    
    get_data($request, $result);                // 部品マスターデータ取得
    
    outViewListHTML($request, $menu, $result);  // ViewのHTMLを出力
    
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
    require_once ('punchMark_partsMasterMnt_View.php');

    ob_end_flush(); 
}

function request_check($request, $result)    // 処理の分岐を行う
{
    $ok = true;
    if ($request->get('entry') != '')  $ok = punchMark_parts_entry($request, $result);
    if ($request->get('change') != '') $ok = punchMark_parts_change($request, $result);
    if ($request->get('del') != '')    $ok = punchMark_parts_del($request);
    if ($ok) {
        // マーカー用にセット
        $result->add('parts_no', $request->get('parts_no'));
        $result->add('punchMark_code', $request->get('punchMark_code'));
    }
    if ($request->get('search') != '') {
        $result->add('where', getSearchCondition($request));
    } elseif ($ok) {
        $request->add('parts_no', '');
        $request->add('punchMark_code', '');
        $request->add('note', '');
    }
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// 追加ロジック
function punchMark_parts_entry($request, $result)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $note      = $request->get('note');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {    //部品番号の登録チェック
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no}はマスターに登録されていません！！";
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s'", $punchMark_code);
    $res_chk = array();
    if (getResult($query, $res_chk) <= 0 ) {   //////// 刻印コード登録なし エラー
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}は登録されていません！！";
        return false;
    }
    $query = sprintf("SELECT parts_no FROM punchMark_parts_master WHERE parts_no='%s' and punchMark_code='%s'", $parts_no, $punchMark_code);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり エラー
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} 刻印コード：{$punchMark_code}はすでに登録されています！";
        return false;
    } else {                                    //////// 登録なし INSERT 新規   
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return true;
}

////////////// 登録 本体 ロジック
function punchMark_entryBody($request)
{
    $query  = "INSERT INTO punchMark_parts_master ";
    $query .= "(parts_no, punchMark_code, note, last_user) ";
    $query .= "VALUES ('{$request->get('parts_no')}', '{$request->get('punchMark_code')}', '{$request->get('note')}', '{$_SESSION['User_ID']}')";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "部品番号：{$request->get('parts_no')} 刻印コード：{$request->get('punchMark_code')}の追加に失敗！";
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "部品番号：{$request->get('parts_no')} 刻印コード：{$request->get('punchMark_code')}を追加しました！";
        // 編集履歴保存
        setEditHistory('punchMark_parts_master', 'I', $query);
    }
    return true;
}

///////////////// 変更ロジック
function punchMark_parts_change($request, $result)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $note      = $request->get('note');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {    //部品番号の登録チェック
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no}はマスターに登録されていません！！";
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s'", $punchMark_code);
    $res_chk = array();
    if (getResult($query, $res_chk) <= 0 ) {   //////// 刻印コード登録なし エラー
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}は登録されていません！！";
        return false;
    }
    $query = sprintf("SELECT * FROM punchMark_parts_master WHERE parts_no='%s' and punchMark_code='%s' ", $parts_no, $punchMark_code);
    $old_data = getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 更新
        $query = sprintf("UPDATE punchMark_parts_master SET parts_no='%s', punchMark_code='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE parts_no='%s' and punchMark_code='%s'", $parts_no, $punchMark_code, $note, $_SESSION['User_ID'], $parts_no, $punchMark_code);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} 刻印コード：{$punchMark_code}の変更失敗！";
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no}刻印コード：{$punchMark_code}を変更しました！";
            // 編集履歴保存
            setEditHistory('punchMark_parts_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// 登録なし INSERT 新規   
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return false;
}

//////////// 削除ボタンが押された時
function punchMark_parts_del($request)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $query = "SELECT * FROM punchMark_parts_master WHERE parts_no = '{$parts_no}' and punchMark_code = '{$punchMark_code}'";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '対象データの取得に失敗！ 管理担当者へ連絡して下さい。';
        return false;
    }
    $query = "DELETE FROM punchMark_parts_master WHERE parts_no = '{$parts_no}' and punchMark_code = '{$punchMark_code}'";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} 刻印コード：{$punchMark_code}の削除に失敗！";
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "部品番号：{$parts_no} 刻印コード：{$punchMark_code}を削除しました！";
        // 編集履歴保存
        setEditHistory('punchMark_parts_master', 'D', $query, $old_data);
        return true;
    }
    return false;
}

////////////// コピーのリンクが押された時  &&を追加 Undefined index対応
function pre_copy($request, $result)
{
    $res = array();
    $parts_no = $request->get('number');
    $punchMark_code = $request->get('punch');;
    $note      = $request->get('notes');;
    $request->add('parts_no', $parts_no);
    $request->add('punchMark_code', $punchMark_code);
    $request->add('note', $note);
}

////////////// 表示用(一覧表)のマスターデータをSQLで取得
function get_data($request, $result)
{
    $query = "
        SELECT  partsm.parts_no             AS 部品番号     -- 0
            ,   partsm.punchMark_code       AS 刻印コード   -- 1
            ,   partsm.note                 AS 備考         -- 2
            ,   shelf_no                    AS 棚番         -- 3
            ,   mark                        AS 刻印内容     -- 4
            ,   shape_name                  AS 形状名       -- 5
            ,   size_name                   AS サイズ名     -- 6
            ,   (SELECT substr(midsc, 1, 10) FROM miitem WHERE mipn=CAST(parts_no AS CHAR(9)) LIMIT 1)
                                            AS 部品名       -- 7
            ,
            CASE
                WHEN lend_flg IS TRUE THEN '貸出中'
                ELSE ''
            END                         AS 貸出状況     -- 8
        FROM
            punchMark_parts_master AS partsm
        -- LEFT OUTER JOIN
        --     miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            punchMark_master USING (punchmark_code)
        LEFT OUTER JOIN
            punchMark_shape_master USING (shape_code)
        LEFT OUTER JOIN
            punchMark_size_master USING (size_code)
        {$result->get('where')}
        ORDER BY
            parts_no, shelf_no, punchmark_code
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "部品番号";
        $field[1]   = "刻印コード";
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

////////////// 刻印マスターのデータをSQLで取得
function get_master($request, $result, $menu)
{
    $query = "
        SELECT  punchm.punchMark_code           AS 刻印コード     -- 0
            ,   punchm.make_flg                 AS 作成中フラグ   -- 1
        FROM
            punchMark_master AS punchm
        ORDER BY
            punchMark_code
        LIMIT 1
    ";

    $res = array();
    $res_punchMark_code = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= "刻印マスターが１件も登録されていません！ 登録を確認してください！";    // .= に注意
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        // $result->add_array2('res_punch', $res);
        // $request->add('rows_punch', $rows);
    }
}

// View用HTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $res        = $result->get_array();
    $field      = $result->get_array2('field');
    $num        = $request->get('num');
    $rows       = $request->get('rows');
    $parts_name = '';
    // 初期化
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../punchMark_MasterMnt.css' type='text/css' media='screen'>\n";
    $listTable .= "<script type='text/javascript' src='../punchMark_partsMasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none;'>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "        <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "    <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $res[-1][0] = '';   // ダミー初期化
    for ($r=0; $r<$rows; $r++) {
        if ($result->get('parts_no') == $res[$r][0] && $result->get('punchMark_code') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } elseif ($request->get('parts_no') == $res[$r][0] && $request->get('punchMark_code') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } else {
            $listTable .= "<tr>\n";
            $Mark = '';
        }
        $listTable .= "    <td class='winbox' width=' 6%' align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        if ($res[$r][2] == '') {
            $listTable .= "        <a {$Mark}href='../punchMark_partsMasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&punch=". urlencode($res[$r][1]) . "' target='_parent' style='text-decoration:none;'>\n";
        } else {
            $listTable .= "        <a {$Mark}href='../punchMark_partsMasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&punch=". urlencode($res[$r][1]) . "&notes=" . urlencode($res[$r][2]) . "' target='_parent' style='text-decoration:none;'>\n";
        }
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        if ($res[$r-1][0] == $res[$r][0]) {
            // 部品番号
            $listTable .= "<td class='winbox pt12b' width='11%' align='center'>&nbsp;</td>\n";
            // 部品名
            $listTable .= "<td class='winbox pt12b' width='18%' align='left' >&nbsp;</td>\n";
        } else {
            // 部品番号
            $listTable .= "<td class='winbox pt12b' width='11%' align='center'>{$res[$r][0]}</td>\n";
            // 部品名
            $listTable .= "<td class='winbox pt12b' width='18%' align='left' >{$res[$r][7]}</td>\n";
        }
        // 棚番
        $listTable .= "<td class='winbox pt12b' width=' 8%' align='center'>{$res[$r][3]}</td>\n";
        // 刻印コード
        $listTable .= "<td class='winbox pt12b' width='10%' align='center'>{$res[$r][1]}</td>\n";
        // 刻印内容
        $tmpView = str_replace("\r", '<br>', $res[$r][4]);
        $listTable .= "<td class='winbox pt12b' width='14%' align='center'>{$tmpView}</td>\n";
        // 形状
        $listTable .= "<td class='winbox pt12b' width=' 6%' align='center'>{$res[$r][5]}</td>\n";
        // サイズ
        $listTable .= "<td class='winbox pt12b' width=' 6%' align='center'>{$res[$r][6]}</td>\n";
        // 備考
        if ($res[$r][8] == '貸出中') {
            $addMsg = "<span style='color:red;'>{$res[$r][8]}</span>";
        } else {
            $addMsg = '';
        }
        if ($res[$r][2] == '') {
            $listTable .= "<td class='winbox pt12b' width='21%' align='left'>{$addMsg}&nbsp;</td>\n";
        } else {
            $listTable .= "<td class='winbox pt12b' width='21%' align='left'>{$addMsg}{$res[$r][2]}</td>\n";
        }
        $listTable .= "</tr>\n";
    }
    $listTable .= "</table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "</td></tr>\n";
    $listTable .= "</table>\n";
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
    $file_name = "list/punchMark_partsMasterMnt_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}

////////// 検索条件の取得
function getSearchCondition($request)
{
    if ($request->get('search') == '') return '';
    $where = '';
    if ($request->get('parts_no') != '') {
        $where .= "WHERE parts_no LIKE '%{$request->get('parts_no')}%'";
    }
    if ($request->get('punchMark_code') != '' && $where != '') {
        $where .= " AND punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    } elseif ($request->get('punchMark_code') != '') {
        $where .= "WHERE punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    }
    if ($request->get('note') != '' && $where != '') {
        $where .= " AND partsm.note LIKE '%{$request->get('note')}%'";
    } elseif ($request->get('note') != '') {
        $where .= "WHERE partsm.note LIKE '%{$request->get('note')}%'";
    }
    return $where;
}

?>
