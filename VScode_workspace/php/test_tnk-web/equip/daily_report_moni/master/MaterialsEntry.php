<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 運転日報の材料マスター保守  親ファイル  Client interface 部 //
// 編集(MaterialEntryPage)・照会(MaterialView)を呼出す  MVC View の List 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   MaterialEntry.php                                   //
// 2006/06/09 access_log() 対応                                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // access_log()等で使用
require_once ('../com/define.php');
require_once ('../com/function.php');
access_log();                               // Script Name は自動取得

// メッセージのクリア
$Message = '';

// 管理者モードの取得
$AdminUser = AdminUser( FNC_MASTER );

// 処理コードの取得
$ProcCode = $_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

// 格納
$Materials = Array();
$Materials['Code']   = trim (@$_REQUEST['Code']);
$Materials['Name']   = trim (@$_REQUEST['Name']);
$Materials['Type']   = trim (@$_REQUEST['Type']);
$Materials['Style']  = trim (@$_REQUEST['Style']);
$Materials['Weight'] = trim (@$_REQUEST['Weight']);
$Materials['Length'] = trim (@$_REQUEST['Length']);


// 処理の振り分け
if ($ProcCode == 'EDIT') {
    $EDIT_MODE = 'INSERT';
    // 材料コードが取得できるときは修正モード
    if ($Materials['Code'] != '') {
        ReadData();
        $EDIT_MODE = 'UPDATE';
    }
    // Entry画面表示
    require_once('MaterialsEntryPage.php');
} else if ($ProcCode == 'WRITE') {
    // 入力内容のチェック
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    if (!EntryDataCheck()) {
        // エラーがあるので入力画面に戻る
        require_once('MaterialsEntryPage.php');
    } else {
        // データの保存
        SaveData();
        // 再読込
        ReadData();
        // 表示画面
        $Message = '登録しました。';
        // 表示画面へ
        require_once('MaterialsView.php');
    }
} else if ($ProcCode == 'DELETE') {
    // 削除モード
    DeleteData();
    // リダイレクト
    header("Location: ".@$_REQUEST['RetUrl']);
    
} else if ($ProcCode == 'VIEW') {
    // 登録内容の読み込み
    ReadData();
    // 表示画面へ
    require_once('MaterialsView.php');
} else {
    // システムエラー
    $SYSTEM_MESSAGE = "処理コードが正しくありません：[$ProcCode]";
    require_once('../com/' . ERROR_PAGE);
    exit();
}

// --------------------------------------------------
// 入力内容のチェック
// --------------------------------------------------
function EntryDataCheck()
{
    global $Message,$Materials,$EDIT_MODE;
    // 材料コード
    if ($Materials['Code'] == '') {
        $Message .= '材料コードが未入力です。\n\n';
    } else {
        // 文字数チェック
        if (strlen($Materials['Code']) > 7) {
            $Message .= '材料コードは７バイト以内で登録して下さい。\n\n';
        } else {
            // 重複登録のチェック
            if ($EDIT_MODE == 'INSERT') {
                $con = getConnection();
                $sql = "select mtcode from equip_materials where mtcode='" .$Materials['Code']."'";
                $rs = pg_query ($con , $sql);
                if ($row = pg_fetch_array ($rs)) {
                    $Message .= '材料コード['.$Materials['Code'].']はすでに登録されています。\n\n';
                }
            }
        }
    }
    
    // 材料名称
    if ($Materials['Name'] == '') {
        $Message .= '材料名称が未入力です。\n\n';
    } else {
        if (strlen($Materials['Name']) > 30) {
            $Message .= '材料名称は３０バイト以内で登録して下さい。\n\n';
        }
    }

    // 材質
    if ($Materials['Style'] == '') {
        $Message .= '部品材質が未入力です。\n\n';
    } else {
        if (strlen($Materials['Style']) > 30) {
            $Message .= '部品材質は３０バイト以内で登録して下さい。\n\n';
        }
    }
    
    // 対重量
    if ($Materials['Weight'] == '') {
        $Message .= '重量が未入力です。\n\n';
    } else {
        if (!is_numeric($Materials['Weight'])) {
            $Message .= '重量は数値で入力して下さい。\n\n';
        } else {
            if ($Materials['Weight'] <= 0) {
                $Message .= '重量は０以下では登録できません\n\n';
            } else {
                $Materials['Weight'] = sprintf ('%.04f', $Materials['Weight']);
            }
        }
    }
    // 標準長さ
    if ($Materials['Length'] == '') {
        $Message .= '標準長さが未入力です。\n\n';
    } else {
        if (!is_numeric($Materials['Length'])) {
            $Message .= '標準長さは数値で入力して下さい。\n\n';
        } else {
            if ($Materials['Length'] <= 0) {
                $Message .= '標準長さは０以下では登録できません\n\n';
            } else {
                $Materials['Length'] = sprintf ('%.04f', $Materials['Length']);
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
// --------------------------------------------------
// 登録
// --------------------------------------------------
function SaveData()
{
    global $Materials;
    
    // コネクションの取得
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    if ($_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // 修正モードの時は一度消す
        $sql = "delete from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    // 保存
    $sql = "insert into equip_materials(mtcode,mtname,type,style,weight,length,last_user) values ( "
         . "'" . pg_escape_string ($Materials['Code'])    . "',"
         . "'" . pg_escape_string ($Materials['Name'])    . "',"
         . "'" . pg_escape_string ($Materials['Type'])    . "',"
         . "'" . pg_escape_string ($Materials['Style'])   . "',"
         . "'" . pg_escape_string ($Materials['Weight'])  . "',"
         . "'" . pg_escape_string ($Materials['Length'])  . "',"
         . "'" . pg_escape_string ($_SESSION['User_ID'])  . "'"
         . " ) ";
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
   
}
// --------------------------------------------------
// 登録データの読み込み
// --------------------------------------------------
function ReadData()
{
    global $Materials;
    
    // コネクションの取得
    $con = getConnection();
    
    // データ取得
    $sql = "select mtname,type,style,weight,length from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
    $rs = pg_query ($con , $sql);
    if ($row = pg_fetch_array ($rs)) {
        $Materials['Name']    = $row['mtname'];
        $Materials['Type']    = $row['type'];
        $Materials['Style']   = $row['style'];
        $Materials['Weight']  = $row['weight'];
        $Materials['Length']  = $row['length'];
    } else {
        $SYSTEM_MESSAGE = "データの取得に失敗しました。\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
   
}
// --------------------------------------------------
// 登録データの削除
// --------------------------------------------------
function DeleteData()
{
    global $Materials;
    
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    $sql = "delete from equip_materials where mtcode='" . pg_escape_string ($Materials['Code']) . "'";
    echo($sql);
    if (!pg_query ($con , $sql)) {
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    pg_query ($con , 'COMMIT');
}
ob_end_flush();
