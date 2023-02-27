<?php 
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理 運転日報の部品マスター保守  親ファイル  Client interface 部 //
//     編集(PartsEntryPage)・照会(PartsView)用を呼出す  MVC View の List 部 //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   PartsEntry.php                                      //
// 2006/06/09 access_log()対応 equip_partsテーブル変更→機械番号と機械名追加//
// 2006/06/12 $ProcCodeで処理振分をif else if → switch () へ変更           //
//            getMacNoSelectData($mac_no), getMachineName($mac_no) を追加   //
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

// 管理者モードの取得
$AdminUser = AdminUser( FNC_MASTER );
// メッセージのクリア
$Message = '';
$CheckMaster = false;

// 処理コードの取得
$ProcCode = @$_REQUEST['ProcCode'];
if (!isset($_REQUEST['ProcCode'])) $ProcCode = 'EDIT';

// 格納
$Parts = Array();
$Parts['MacNo']         = trim(@$_REQUEST['MacNo']);
$Parts['MacName']       = trim(@$_REQUEST['MacName']);  // Undefined index を避けるために追加
$Parts['Code']          = strtoupper(trim(@$_REQUEST['Code']));
$Parts['Name']          = trim(@$_REQUEST['Name']);
$Parts['Zai']           = trim(@$_REQUEST['Zai']);
$Parts['Size']          = trim(@$_REQUEST['Size']);
$Parts['UseItem']       = trim(@$_REQUEST['UseItem']);
$Parts['Abandonment']   = trim(@$_REQUEST['Abandonment']);

// 処理の振り分け
switch ($ProcCode) {
case 'EDIT':
    $EDIT_MODE = 'INSERT';
    // 材料コードが取得できるときは修正モード
    if ($Parts['Code'] != '') {
        // データの読み込み
        ReadData();
        // 修正モードセット
        $EDIT_MODE = 'UPDATE';
    }
    // Entry画面表示
    require_once('PartsEntryPage.php');
    break;
case 'WRITE':
    $CheckMaster = $_REQUEST['CheckMaster'];
    // 入力内容のチェック
    $EDIT_MODE = @$_REQUEST['EDIT_MODE'];
    if (!EntryDataCheck()) {
        // エラーがあるので入力画面に戻る
        require_once('PartsEntryPage.php');
    } else {
        // データの保存
        SaveData();
        // データの再読込
        ReadData();
        // 表示画面
        $Message = '登録しました。';
        // 表示ページに移動
        require_once('PartsView.php');
    }
    break;
case 'CHECK_MASTER':
    $EDIT_MODE = $_REQUEST['EDIT_MODE'];
    EntryDataCheck();
    $Message = '';
    $Parts['UseItem'] = '';
    if ($Parts['Code'] != '') {
        // コネクションの取得
        $con = getConnection();     // 以下は最新の材料コードにするため ORDER BY delivery DESC を追加 2006/06/12
        $rs = pg_query ($con , "SELECT material FROM equip_work_inst_header WHERE parts_no='" . pg_escape_string ($Parts['Code']) . "' ORDER BY delivery DESC");
        if ($row = pg_fetch_array ($rs)) {
            $Parts['UseItem'] = $row['material'];
            $CheckMaster = true;
        }
    }
    // 入力画面に戻る
    require_once('PartsEntryPage.php');
    break;
case 'CHECK_MAC_MASTER':
    $EDIT_MODE = $_REQUEST['EDIT_MODE'];
    $Message = '';
    $Parts['MacName'] = getMachineName($Parts['MacNo']);
    // 入力画面に戻る
    require_once('PartsEntryPage.php');
    break;
case 'DELETE':
    // データの削除
    DeleteData();
    // リダイレクト
    header("Location: ".@$_REQUEST['RetUrl']);
    break;
case 'VIEW':
    // データの読み込み
    ReadData();
    // 表示ページに移動
    require_once('PartsView.php');
    break;
default:
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
    global $Message,$Parts,$EDIT_MODE;
    
    // コネクションの取得
    $con = getConnection();
    // 部品コード
    if ($Parts['Code'] == '') {
        $Message .= '部品番号が未入力です。\n\n';
    } else {
        // 既存のアイテムマスタ存在チェック
        $sql = "SELECT mipn, midsc, mzist FROM miitem WHERE mipn='" . pg_escape_string ($Parts['Code'])."'";
        $rs = pg_query ($con , $sql);
        if ($row = pg_fetch_array ($rs)) {
            // マスタに存在するので値を格納
            $Parts['Name'] = $row['midsc'];
            $Parts['Zai']  = $row['mzist'];
        } else {
            // マスタチェックエラー
            $Message .= "部品番号 [{$Parts['Code']}] はマスタに登録されていません\\n\\n";
            $Parts['Name'] = '';
            $Parts['Zai']  = '';
        }
        // 重複登録のチェック
        if ($EDIT_MODE == 'INSERT') {
            $sql = "SELECT item_code FROM equip_parts WHERE item_code='" .$Parts['Code']."' AND mac_no={$Parts['MacNo']}";
            $rs = pg_query ($con , $sql);
            if ($row = pg_fetch_array ($rs)) {
                $Message .= "機械番号 [{$Parts['MacNo']}] の 部品番号 [{$Parts['Code']}] はすでに登録されています。\\n\\n";
            }
        }
    }
    
    // 寸法
    if ($Parts['Size'] == '') {
        $Message .= '寸法が未入力です。\n\n';
    } else {
        if (!is_numeric($Parts['Size'])) {
            $Message .= '寸法は数値で入力して下さい。\n\n';
        } else {
            if ($Parts['Size'] <= 0) {
                $Message .= '寸法は０以下では登録できません。\n\n';
            }
        }
    }
    // 使用材料
    if ($Parts['UseItem'] == '') {
        $Message .= '使用材料が未入力です。\n\n';
    }
    
    // 破材サイズ
    if ($Parts['Abandonment'] == '') {
        $Message .= '破材サイズが未入力です。\n\n';
    } else {
        if (!is_numeric($Parts['Abandonment'])) {
            $Message .= '破材サイズは数値で入力して下さい。\n\n';
        } else {
            if ($Parts['Abandonment'] < 0) {
                $Message .= '破材は０未満では登録できません。\n\n';
            }
        }
    }
    
    if ($Message == '') return true;
    else                return false;
}
// --------------------------------------------------
// データの保存
// --------------------------------------------------
function SaveData()
{
    global $Parts;
    
    // コネクションの取得
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    if (@$_REQUEST['EDIT_MODE'] == 'UPDATE') {
        // 修正モードの時は一度消す
        $sql = "DELETE FROM equip_parts WHERE item_code='" . pg_escape_string ($Parts['Code']) . "' AND mac_no={$Parts['MacNo']}";
        if (!pg_query ($con , $sql)) {
            pg_query ($con , 'ROLLBACK');
            $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
            require_once ('../com/' . ERROR_PAGE);
            exit();
        }
    }
    
    // データ保存
    $sql = "INSERT INTO equip_parts(mac_no, item_code, size, use_item, abandonment, last_user) values ( "
         .       pg_escape_string ($Parts['MacNo'])        . " ,"
         . "'" . pg_escape_string ($Parts['Code'])         . "',"
         . "'" . pg_escape_string ($Parts['Size'])         . "',"
         . "'" . pg_escape_string ($Parts['UseItem'])      . "',"
         . "'" . pg_escape_string ($Parts['Abandonment'])  . "',"
         . "'" . pg_escape_string ($_SESSION['User_ID'])   . "'"
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
// データの読み込み 
// --------------------------------------------------
function ReadData()
{
    global $Parts;
    
    // コネクションの取得
    $con = getConnection();
    
    $sql = "
        SELECT
            to_char(equip_parts.mac_no, 'FM0000')
                                    AS mac_no           ,
            CASE
                WHEN equip_parts.mac_no = 0 THEN '共用データ(初期値)'
                ELSE mac_master.mac_name
            END                     AS mac_name         ,
            equip_parts.item_code   AS item_code        ,
            miitem.midsc            AS item_name        ,
            miitem.mzist            AS zai              ,
            equip_parts.size        AS size             ,
            equip_parts.use_item    AS use_item         ,
            equip_parts.abandonment AS abandonment
        FROM
            equip_parts
        LEFT OUTER JOIN miitem ON (equip_parts.item_code = miitem.mipn)
        LEFT OUTER JOIN equip_machine_master2 AS mac_master USING (mac_no)
        WHERE
            equip_parts.item_code = '" . pg_escape_string ($Parts['Code']) . "'
            AND equip_parts.mac_no = " . pg_escape_string ($Parts['MacNo']) . " 
    ";

    $rs = pg_query ($con , $sql);
    
    if ($row = pg_fetch_array ($rs)) {
        // 値の格納
        $Parts['MacNo']   = $row['mac_no'];
        $Parts['MacName'] = $row['mac_name'];
        $Parts['Code']    = $row['item_code'];
        $Parts['Name']    = $row['item_name'];
        $Parts['Zai']     = $row['zai'];
        $Parts['Size']    = $row['size'];
        $Parts['UseItem'] = $row['use_item'];
        $Parts['Abandonment']  = $row['abandonment'];
    } else {
        // システムエラー
        $SYSTEM_MESSAGE = "データの取得に失敗しました。\n$sql";
        require_once ('../com/'.ERROR_PAGE);
        exit();
    }
   
}
// --------------------------------------------------
// データの削除      
// --------------------------------------------------
function DeleteData()
{
    global $Parts;
    
    // コネクションの取得
    $con = getConnection();
    pg_query ($con , 'BEGIN');
    
    $sql = "DELETE FROM equip_parts WHERE item_code='" . pg_escape_string ($Parts['Code']) . "' AND mac_no={$Parts['MacNo']}";
    echo($sql);
    if (!pg_query ($con , $sql)) {
        // システムエラー
        pg_query ($con , 'ROLLBACK');
        $SYSTEM_MESSAGE = "データベースの更新に失敗しました\n$sql";
        require_once ('../com/' . ERROR_PAGE);
        exit();
    }
    
    pg_query ($con , 'COMMIT');
}
// --------------------------------------------------
// 機械番号のselectデータ取得
// --------------------------------------------------
function getMacNoSelectData($mac_no)
{
    if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
    if ($factory != '') {
        $query = "
            SELECT to_char(mac_no, 'FM0000'), substr(mac_name, 1, 10) FROM equip_machine_master2 WHERE survey = 'Y' AND factory = '{$factory}'
        ";
    } else {
        $query = "
            SELECT to_char(mac_no, 'FM0000'), substr(mac_name, 1, 10) FROM equip_machine_master2 WHERE survey = 'Y'
        ";
    }
    // 初期化
    $option = "\n";
    $res = array();
    $rows = getResult2($query, $res);
    if ($mac_no == '0000') {
        $option .= "<option value='0000' selected>0000 共用データ</option>\n";
    } else {
        $option .= "<option value='0000'>0000 共用データ</option>\n";
    }
    for ($i=0; $i<$rows; $i++) {
        if ($mac_no == $res[$i][0]) {
            $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][0]} {$res[$i][1]}</option>\n";
        } else {
            $option .= "<option value='{$res[$i][0]}'>{$res[$i][0]} {$res[$i][1]}</option>\n";
        }
    }
    return $option;
}
// --------------------------------------------------
// 機械番号のselectデータ取得
// --------------------------------------------------
function getMachineName($mac_no)
{
    if ($mac_no == '0000') return '共用データ(初期値)';
    $query = "
        SELECT mac_name FROM equip_machine_master2 WHERE mac_no = {$mac_no}
    ";
    $mac_name = '';
    getUniResult($query, $mac_name);
    return $mac_name;
}

ob_end_flush();
