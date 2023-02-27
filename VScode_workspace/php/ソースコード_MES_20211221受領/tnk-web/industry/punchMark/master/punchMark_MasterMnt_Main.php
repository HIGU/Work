<?php
//////////////////////////////////////////////////////////////////////////////
// 刻印管理 刻印マスター メンテナンス メイン部                              //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/07/26 Created   punchMark_MasterMnt_Main.php                        //
// 2007/09/25 header('Location: $menu->RetUrl()') →                        //
//            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl())  小林 //
// 2007/09/26 site_index を INDEX_INDUST へ変更  小林                       //
// 2007/10/02 刻印コードの重複チェックを外し登録できるように変更            //
// 2007/10/05 完了・取消をボタンからリンクに変更                            //
// 2007/10/18 getViewHTMLbody()に<body>タグ抜け及びmenu_form.cssを追加      //
//            刻印内容を$tmpViewで入力されたとおりに表示 E_ALL | E_STRICTへ //
//            刻印コードと刻印内容でのソート機能を追加                 小林 //
// 2007/10/19 棚番順追加 デザイン変更(ヘッダーをインラインへ)及び検索追加   //
//            全項目を対象に検索機能を追加                             小林 //
// 2007/10/20 余分な</tr>を削除 get_master()の部品マスター取得をコメントOUT //
//            punchMark_del()の部品マスターと自分のチェックロジック変更 小林//
// 2007/10/23 cellpadding='3' → cellpadding='1' へ変更(CSSも変更)      小林//
// 2007/10/24 プログラムの最後に改行を追加                                  //
// 2007/11/08 同じ刻印コードが登録された場合の刻印内容の同一性チェック関数  //
//            punchMarkSameCheck()を追加（棚番と備考は変更可)               //
//            上記関数のSQL文に LIMIT 1 を追加  小林                        //
// 2007/11/09 header('Location: ' . H_WEB_HOST . $menu->out_self());削除小林//
//            getPreDataRows()とsetEditHistory()編集履歴を追加。        小林//
//            客先コードのフォーマットを %d → %s  %05dを廃止           小林//
//            INSERT INTO文を punchMark_entryBody()へ統合               小林//
// 2007/11/10 余分な<font color='yellow'></font>を削除                  小林//
//            putErrorLogWrite()を使いしてSQLエラーのdebugを行う        小林//
//            Markを追加し行マーカーとジャンプをする                    小林//
// 2007/11/16 刻印コードが同じ場合は刻印コードを表示しないを追加        小林//
// 2008/09/03 貸し出し中の場合は備考欄に表示するように変更                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
//ini_set('error_reporting', E_STRICT);          // E_ALL='2047' debug 用
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
    $menu->set_title('刻印管理 刻印マスター メンテナンス');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result, $menu);       // 削除確認用のマスターデータ取得
    
    request_check($request, $result);           // 処理の分岐
    
    get_data($request, $result);                // 刻印マスターのデータ取得
    
    outViewListHTML($request, $menu, $result);  // View用HTMLの出力
    
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
    require_once ('punchMark_MasterMnt_View.php');

    ob_end_flush(); 
}

function request_check($request, $result)    //処理の分岐を行う
{
    $ok = true;
    if ($request->get('entry') != '')  $ok = punchMark_entry($request);
    if ($request->get('change') != '') $ok = punchMark_change($request);
    if ($request->get('del') != '')    $ok = punchMark_del($request, $result);
    if ($request->get('finish') != '') $ok = punchMark_finish($request);
    if ($request->get('cancel') != '') $ok = punchMark_cancel($request, $result);
    if ($ok) {
        // マーカー用にセット
        $result->add('punchMark_code', $request->get('punchMark_code'));
        $result->add('shelf_no', $request->get('shelf_no'));
    }
    if ($request->get('search') != '') {
        $result->add('where', getSearchCondition($request));
    } elseif ($ok) {
        $request->add('punchMark_code', '');
        $request->add('shelf_no', '');
        $request->add('mark', '');
        $request->add('shape_code', '');
        $request->add('size_code', '');
        $request->add('user_code', '');
        $request->add('note', '');
        $request->add('make_flg', '');
    }
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// 登録ロジック
function punchMark_entry($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $mark = $request->get('mark');
    $shape_code = $request->get('shape_code');
    $size_code = $request->get('size_code');
    $user_code = $request->get('user_code');
    $note      = $request->get('note');
    $query = sprintf("SELECT * FROM punchMark_master WHERE shelf_no='%s'", $shelf_no);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり エラー
        $_SESSION['s_sysmsg'] .= "棚番：{$shelf_no}はすでに使用されています！";    // .= に注意
        return false;
    }
    if (!punchMarkSameCheck($request)) {    /////// 同じ刻印コードを登録する場合の刻印内容の同一性チェック（棚番と備考以外は同じ内容)
        $_SESSION['s_sysmsg'] .= "刻印の内容が変更されています！同じ刻印コードを登録する場合は、棚番と備考以外変更しないで下さい！";    // .= に注意
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり エラー
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code} 棚番：{$shelf_no}はすでに登録されています！";    // .= に注意
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
    $punchMark_code = $request->get('punchMark_code');
    $query  = "INSERT INTO punchMark_master ";
    $query .= "(punchMark_code, shelf_no, mark, shape_code, size_code, user_code, note, make_flg, lend_flg, last_date, last_user) ";
    $query .= "VALUES ('{$punchMark_code}', '{$request->get('shelf_no')}', '{$request->get('mark')}', {$request->get('shape_code')}, ";
    $query .= "{$request->get('size_code')}, '{$request->get('user_code')}', '{$request->get('note')}', TRUE, FALSE,  CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}の追加に失敗！";    // .= に注意
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}を追加しました！";
        // 編集履歴保存
        setEditHistory('punchMark_master', 'I', $query);
    }
    return true;
}

////////////// 変更ロジック
function punchMark_change($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $mark = $request->get('mark');
    $shape_code = $request->get('shape_code');
    $size_code = $request->get('size_code');
    $user_code = $request->get('user_code');
    $note      = $request->get('note');
    if (!punchMarkSameCheck($request, 'change')) {    // 同じ刻印コードを登録する場合の刻印内容の同一性チェック（棚番と備考以外は同じ内容)
        $_SESSION['s_sysmsg'] .= "刻印の内容が変更されています！同じ刻印コードを登録する場合は、棚番と備考以外変更しないで下さい！";    // .= に注意
        return false;
    }
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    $old_data = getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 更新
        $query = sprintf("UPDATE punchMark_master SET punchMark_code='%s', shelf_no='%s', mark='%s', shape_code=%d, size_code=%d, user_code='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' and shelf_no='%s'", $punchMark_code, $shelf_no, $mark, $shape_code, $size_code, $user_code, $note, $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}の変更失敗！";    // .= に注意
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}を変更しました！";
            // 編集履歴保存
            setEditHistory('punchMark_master', 'U', $query, $old_data);
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
function punchMark_del($request, $result)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no       = $request->get('shelf_no');
    ///// 部品マスターと自分(刻印マスター)の登録状況をチェック
    $query = "SELECT parts_no FROM punchMark_parts_master WHERE punchMark_code='{$punchMark_code}'";
    if (getResult2($query, $check) > 0) {   // 部品マスターに登録されていて
        $query = "SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='{$punchMark_code}'";
        if (getResult2($query, $check) <= 1) {  // 刻印マスターが残り１個になったら削除できないメッセージ出力
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}は部品マスターで使用されています！！先に部品マスターを削除して下さい。";
            return false;
        }
    }
    $query = "SELECT * FROM punchMark_master WHERE punchMark_code = '{$punchMark_code}' AND shelf_no = '{$shelf_no}'";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '対象データの取得に失敗！ 管理担当者へ連絡して下さい。';
        return false;
    }
    $query = sprintf("DELETE FROM punchMark_master WHERE punchMark_code ='%s' AND shelf_no ='%s'", $punchMark_code, $shelf_no);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}の削除に失敗！";    // .= に注意
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}を削除しました！";
        // 編集履歴保存
        setEditHistory('punchMark_master', 'D', $query, $old_data);
    }
    return true;
}

//////////// 完成ボタンが押された時
function punchMark_finish($request)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no = $request->get('shelf_no');
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '対象データの取得に失敗！ 管理担当者へ連絡して下さい。';
        return false;
    }
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 更新
        $query = sprintf("UPDATE punchMark_master SET make_flg=FALSE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' AND shelf_no='%s'", $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}が作成完了出来ません！";    // .= に注意
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}の作成が完了しました！";
            // 編集履歴保存
            setEditHistory('punchMark_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// 登録なし エラー
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}が作成完了出来ません！";    // .= に注意
    }
    return false;
}

//////////// 製作済 取消ボタンが押された時
function punchMark_cancel($request, $result)
{
    $punchMark_code = $request->get('punchMark_code');
    $shelf_no       = $request->get('shelf_no');
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' AND shelf_no='%s'", $punchMark_code, $shelf_no);
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '対象データの取得に失敗！ 管理担当者へ連絡して下さい。';
        return false;
    }
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 更新
        $query = sprintf("UPDATE punchMark_master SET make_flg=TRUE, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE punchMark_code='%s' AND shelf_no='%s'", $_SESSION['User_ID'], $punchMark_code, $shelf_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}が完了取消し出来ません！";    // .= に注意
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}の完了を取消しました！";
            // 編集履歴保存
            setEditHistory('punchMark_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// 登録なし エラー
        $_SESSION['s_sysmsg'] .= "刻印コード：{$punchMark_code}が完了取消し出来ません！";    // .= に注意
    }
    return false;
}

////////////// コピーのリンクが押された時  &&を追加 Undefined index対応
function pre_copy($request, $result)
{
    $res = array();
    $punchMark_code = $request->get('number');
    $shelf_no   = $request->get('shelf');
    $request->add('punchMark_code', $punchMark_code);
    $query = "SELECT * FROM punchMark_master WHERE punchMark_code='{$punchMark_code}' AND shelf_no='{$shelf_no}'";
    if (getResult($query, $res) <= 0) putErrorLogWrite($query);
    $mark       = $res[0]['mark'];
    $shape_code = $res[0]['shape_code'];
    $size_code  = $res[0]['size_code'];
    $user_code  = $res[0]['user_code'];
    $note       = $res[0]['note'];
    $request->add('shelf_no', $shelf_no);
    $request->add('mark', $mark);
    $request->add('shape_code', $shape_code);
    $request->add('size_code', $size_code);
    $request->add('user_code', $user_code);
    $request->add('note', $note);
}

////////////// 表示用(一覧表)の刻印マスターデータをSQLで取得
function get_data($request, $result)
{
    $query = "
        SELECT  punchm.punchMark_code           AS 刻印コード     -- 0
            ,   punchm.shelf_no                 AS 棚番           -- 1
            ,   punchm.mark                     AS 刻印内容       -- 2
            ,   punchm.shape_code               AS 形状コード     -- 3
            ,   punchm.size_code                AS サイズコード   -- 4
            ,   punchm.user_code                AS 客先コード     -- 5
            ,   punchm.note                     AS 備考           -- 6
            ,   punchm.make_flg                 AS 作成中フラグ   -- 7
            ,
            CASE
                WHEN lend_flg IS TRUE THEN '貸出中'
                ELSE ''
            END                         AS 貸出状況     -- 8
        FROM
            punchMark_master AS punchm
        {$result->get('where')}
        ORDER BY
            -- mark ASC
            -- punchMark_code ASC
    ";
    if ($request->get('targetSortItem') == 'code') {
        $query .= '            punchMark_code ASC';
    } elseif ($request->get('targetSortItem') == 'shelf') {
        $query .= '            shelf_no ASC';
    } else {
        $request->add('targetSortItem', 'mark');
        $query .= '            mark ASC';
    }
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "刻印コード";
        $field[1]   = "棚番";
        $field[2]   = "刻印内容";
        $field[3]   = "形状コード";
        $field[4]   = "サイズコード";
        $field[5]   = "客先コード";
        $field[6]   = "備考";
        $field[7]   = "作成中フラグ";
        $num = 8;
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

////////////// 削除確認用にサイズマスターと形状マスターと部品番号別刻印マスターのデータをSQLで取得
function get_master($request, $result, $menu)
{
    $query = "
        SELECT  sizem.size_code                AS サイズコード     -- 0
            ,   sizem.size_name                AS サイズ名         -- 1
            ,   sizem.note                     AS 備考             -- 2
        FROM
            punchMark_size_master AS sizem
        ORDER BY
            size_code
    ";

    $res = array();
    $res_size_code = array();
    $res_size_name = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= "サイズマスターが１件も登録されていません！ 登録を確認してください！";    // .= に注意
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $result->add_array2('res_size', $res);
        $request->add('rows_size', $rows);
    }
    
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
        $_SESSION['s_sysmsg'] .= "形状マスターが１件も登録されていません！ 登録を確認してください！";    // .= に注意
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    } else {
        $result->add_array2('res_shape', $res);
        $request->add('rows_shape', $rows);
    }
    /****************** 以下の大量読み込みはＮＧ
    $query = "
        SELECT  partsm.parts_no               AS 部品番号     -- 0
            ,   partsm.punchMark_code         AS 刻印コード   -- 1
            ,   partsm.note                   AS 備考         -- 2
        FROM
            punchMark_parts_master as partsm
        ORDER BY
            parts_no
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) > 0) {
        $result->add_array2('res_parts', $res);
        $request->add('rows_parts', $rows);
    }
    ******************/
}

// View用HTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $res         = $result->get_array();
    $field       = $result->get_array2('field');
    $num         = $request->get('num');
    $rows        = $request->get('rows');
    $res_shape   = $result->get_array2('res_shape');
    $rows_shape  = $request->get('rows_shape');
    $res_size    = $result->get_array2('res_size');
    $rows_size   = $request->get('rows_size');
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
    $listTable .= "<script type='text/javascript' src='../punchMark_MasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none;'>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "        <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "    <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    for ($r=0; $r<$rows; $r++) {
        if ($result->get('punchMark_code') == $res[$r][0] && $result->get('shelf_no') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } elseif ($request->get('punchMark_code') == $res[$r][0] && $request->get('shelf_no') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } else {
            $listTable .= "<tr>\n";
            $Mark = '';
        }
        $listTable .= "    <td class='winbox' width=' 6%' align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        $listTable .= "        <a {$Mark}href='../punchMark_MasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&shelf=". urlencode($res[$r][1]) . "&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;'>\n";
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        $res[-1][0] = '';   // ダミー初期化
        for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
            // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
            switch ($i) {
            case 0:     // 刻印コード
                if ($res[$r-1][$i] == $res[$r][$i]) {
                    $listTable .= "<td class='winbox pt12b' width='10%' align='center'>&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='10%' align='center'>{$res[$r][$i]}</td>\n";
                }
                $listTable .= "<input type='hidden' name='punchMark_code' value='{$res[$r][$i]}'>\n";
                break;
            case 1:     // 棚番
                $listTable .= "<td class='winbox pt12b' width='8%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 2:     // 刻印内容
                $tmpView = str_replace("\r", '<br>', $res[$r][$i]);
                $listTable .= "<td class='winbox pt12b' width='14%' align='center'>{$tmpView}</td>\n";
                break;
            case 3:     // 形状コード
                for ($sh=0; $sh<$rows_shape; $sh++) {
                    if ($res_shape[$sh][0] == $res[$r][$i]) {
                        $shape_name = $res_shape[$sh][1];
                        break;
                    } else {
                        $shape_name = '未設定';
                    }
                }
                $listTable .= "<td class='winbox pt12b' width=' 7%' align='center'>{$shape_name}</td>\n";
                break;
            case 4:     // サイズコード
                for ($si=0; $si<$rows_size; $si++) {
                    if ($res_size[$si][0] == $res[$r][$i]) {
                        $size_name = $res_size[$si][1];
                        break;
                    } else {
                        $size_name = '未設定';
                    }
                }
                $listTable .= "<td class='winbox pt12b' width=' 7%' align='center'>{$size_name}</td>\n";
                break;
            case 5:     // 客先コード
                // $user_code = sprintf("%05d", $res[$r][$i]);
                if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                $listTable .= "<td class='winbox pt12b' width=' 8%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 6:     // 備考
                if ($res[$r][8] == '貸出中') {
                    $addMsg = "<span style='color:red;'>{$res[$r][8]}</span>";
                } else {
                    $addMsg = '';
                }
                if ($res[$r][$i] == '') {
                    $listTable .= "<td class='winbox pt12b' width='24%' align='left'>{$addMsg}&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='24%' align='left'>{$addMsg}{$res[$r][$i]}</td>\n";
                }
                break;
            case 7:     // 作成中フラグ
                if ($res[$r][$i] == 't') {
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<span class='pt12br'>製作中　</span>";
                    $listTable .= "</td>\n";
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<a href='../punchMark_MasterMnt_Main.php?finish=1&shelf_no={$res[$r][1]}&punchMark_code={$res[$r][0]}&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;' class='button'>完了</a>\n";
                    $listTable .= "</td>\n";
                } else {
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<span class='pt12bb'>製作済</span>";
                    $listTable .= "</td>\n";
                    $listTable .= "<td class='winbox' align='center' width=' 8%'>\n";
                    $listTable .= "<a href='../punchMark_MasterMnt_Main.php?cancel=1&shelf_no={$res[$r][1]}&punchMark_code={$res[$r][0]}&targetSortItem={$request->get('targetSortItem')}' target='_parent' style='text-decoration:none;' class='button'>取消</a>\n";
                    $listTable .= "</td>\n";
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
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

// View用HTMLファイルの出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    // HTMLファイル出力
    $file_name = "list/punchMark_MasterMnt_List-{$_SESSION['User_ID']}.html";
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
    if ($request->get('punchMark_code') != '') {
        $where .= "WHERE punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    }
    if ($request->get('shelf_no') != '' && $where != '') {
        $where .= " AND shelf_no LIKE '%{$request->get('shelf_no')}%'";
    } elseif ($request->get('shelf_no') != '') {
        $where .= "WHERE shelf_no LIKE '%{$request->get('shelf_no')}%'";
    }
    if ($request->get('mark') != '' && $where != '') {
        $where .= " AND mark LIKE '%{$request->get('mark')}%'";
    } elseif ($request->get('mark') != '') {
        $where .= "WHERE mark LIKE '%{$request->get('mark')}%'";
    }
    if ($request->get('shape_code') != '' && $where != '') {
        $where .= " AND shape_code LIKE '%{$request->get('shape_code')}%'";
    } elseif ($request->get('shape_code') != '') {
        $where .= "WHERE shape_code LIKE '%{$request->get('shape_code')}%'";
    }
    if ($request->get('size_code') != '' && $where != '') {
        $where .= " AND size_code LIKE '%{$request->get('size_code')}%'";
    } elseif ($request->get('size_code') != '') {
        $where .= "WHERE size_code LIKE '%{$request->get('size_code')}%'";
    }
    if ($request->get('user_code') != '' && $where != '') {
        $where .= " AND user_code LIKE '%{$request->get('user_code')}%'";
    } elseif ($request->get('user_code') != '') {
        $where .= "WHERE user_code LIKE '%{$request->get('user_code')}%'";
    }
    if ($request->get('note') != '' && $where != '') {
        $where .= " AND note LIKE '%{$request->get('note')}%'";
    } elseif ($request->get('note') != '') {
        $where .= "WHERE note LIKE '%{$request->get('note')}%'";
    }
    return $where;
}

//////////// 刻印コードが同じ物を登録する場合の内容の変更チェック(備考・棚番は変更可)
function punchMarkSameCheck($request, $flg='')
{
    $query = sprintf("SELECT * FROM punchMark_master WHERE punchMark_code='%s' LIMIT 2", $request->get('punchMark_code'));
    $res_chk = array();
    if ( ($rows=getResult($query, $res_chk)) > 0 ) {
        if ($flg == 'change' && $rows == 1) return true;    // 自分自身のみの変更は許可する
        if ( $request->get('mark') !== $res_chk[0]['mark'] ) {              //////// 刻印内容変更のチェック
            return false;
        }
        if ( $request->get('shape_code') !== $res_chk[0]['shape_code'] ) {  //////// 形状変更のチェック
            return false;
        }
        if ( $request->get('size_code') !== $res_chk[0]['size_code'] ) {    //////// サイズ変更のチェック
            return false;
        }
        if ( $request->get('user_code') !== $res_chk[0]['user_code'] ) {    //////// 客先変更のチェック
            return false;
        }
    }
    return true;
}

?>
