<?php
//////////////////////////////////////////////////////////////////////////////
// データベース接続用 funcotion file PostgreSQL                             //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  pgsql.php                                            //
// 2001/10/01 pg_Connect() pg_pConnect()に変更 (持続的接続)                 //
//            意味があるのか?疑問が残るpg_close()はpg_pconnectには作用しない//
// 2002/12/09 pg_FreeResult() → pg_Free_Result()へ変更                     //
//            pg_Exec()       → pg_query() へ変更                          //
//            pg_NumRows()    → pg_num_rows() へ変更                       //
//                                上記は全て PHP 4.2.0 以上の変更           //
// 2002/12/11 $query=StripSlashes($query) をコメント \のクォート対処        //
// 2003/02/12 pg_Fetch_Array getRowdata()をそのままにして(互換性のため)     //
//            pg_Fetch_row に変えた getRowdata2() を追加                    //
//                       数値インデックスのみで foreach()に使用             //
// 2003/05/01 システム管理のＤＢ処理用にexecQuery2()を新規作成              //
// 2004/01/07 unexpected EOF on client connection対策のため disConnectDBの  //
//            pg_free_result()をコメントにした。                            //
// 2005/05/20 connectDB()にローカル(Unixソケット)接続の機能を追加及び見直し //
//////////////////////////////////////////////////////////////////////////////
define('NOTCONNECT',    '1');
define('EMPTYRESULT',   '2');
define('FAILEDCONNECT', '3');
define('FAILEDQUERY',   '4');

$gConnect   = 0;
$gResult    = 0;
$gLastError = 0;

/* データベースへ接続  */
function connectDB($host, $port, $name, $user, $passwd) {
    global $gConnect;
    global $gResult;
    global $gLastError;

    $gConnect = $gResult = 0;
    if (DB_HOST == 'local') {
        $connstr = 'dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    } else {
        $connstr = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    }
    if ($conn=pg_pConnect($connstr)) {  // 持続的接続へ変更
    // if ($conn=pg_Connect($connstr)) {
        // pg_set_client_encoding($conn, 'EUC-JP');
        $gConnect = $conn;
        return $conn;
    }
    $gLastError = FAILEDCONNECT;
    return $conn;   // FALSE
}

/* データベースを切断  */
function disConnectDB(){
    global $gConnect;
    global $gResult;
    if($gResult){
        // pg_Free_Result($gResult);
        $gResult=0;
    }
    pg_Close($gConnect);
    $gConnect=0;
}

/* クエリーを実行 */
function execQuery($query)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    $gResult = 0;
//  $query = StripSlashes($query);
    if ($gConnect) {
        $res = pg_query($gConnect, $query);
        if ($res) {
            $gResult = $res;
            return pg_Num_Rows($res);
        }
        $gLastError = FAILEDQUERY;
    } else {
        $gLastError = NOTCONNECT;
        // echo "$query \n";   // 接続エラーで呼出元でチェックしているため意味がない
    }
    return -1;
}

/* クエリーを実行 システム管理用のＤＢ処理専用 */
/* @pg_query にして $php_errormsg でエラーをブラウザーに出力 */
function execQuery2($query)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    $gResult = 0;
//  $query=StripSlashes($query);
    if ($gConnect) {
        $res = @pg_query($gConnect, $query);    // @ でエラーメッセージ抑止
        if ($res) {
            $gResult = $res;
            return pg_Num_Rows($res);
        } else {
            echo "<tr><td>\n";
            if (isset($php_errormsg)) {
                echo "<font color='#ff1e00'>" . $php_errormsg . "</font><br>\n";      // ブラウザー出力
            } else {
                echo "<font color='#ff1e00'>php.ini の track_errors = Off を On にして下さい！</font><br>\n";      // ブラウザー出力
            }
            echo "</td></tr>\n";
        }
        $gLastError = FAILEDQUERY;
    } else {
        $gLastError = NOTCONNECT;
    }
    return -1;
}

/* 指定レコードの値を返す 数値インデックス + フィールド名キーの連想インデックス 2003/02/12 */
function getRowdata($row, &$rowdata)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    if($gResult){
        $rowdata = pg_Fetch_Array($gResult, $row);
        return count($rowdata);
    }
    $gLastError = EMPTYRESULT;
    return 0;
}

/* 指定レコードの値を返す 数値インデックスのみ foreach に使用 2003/02/12 */
function getRowdata2($row, &$rowdata)
{
    global $gConnect;
    global $gResult;
    global $gLastError;
    if ($gResult) {
        $rowdata = pg_Fetch_row($gResult, $row);
        return count($rowdata);
    }
    $gLastError = EMPTYRESULT;
    return 0;
}

/* 最後に発生したエラーを文字列として返す */
function getLastError(){
    global $gLastError;
    switch($gLastError){
    case NOTCONNECT:    $str="データベースと接続されていません";    break;
    case EMPTYRESULT:   $str="問い合わせの結果がありません";        break;
    case FAILEDCONNECT: $str="データベースの接続に失敗しました";    break;
    case FAILEDQUERY:   $str="データベースへの問い合わせに失敗しました";break;
    default:        $str="定義されていないエラーです";
    }
    $gLastError=0;
    return $str;
}

/* フィールド名を取得する */
function getFieldsName(&$res_array){
    global $gConnect;
    global $gResult;
    if($gConnect){
        $fields=pg_NumFields($gResult);
        for($i=0;$i<$fields;$i++)
            $res_array[$i]=pg_FieldName($gResult,$i);
        return $fields;
    }
    return 0;
}
?>
