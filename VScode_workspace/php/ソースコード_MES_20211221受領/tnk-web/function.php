<?php
//////////////////////////////////////////////////////////////////////////////////
// TNK Web System 全体 ＤＢインターフェース・その他 汎用関数                    //
// function.php                                                                 //
// Copyright (C) 2001-2007 Kazuhiro.Kobayashi all rights reserved.              //
//                                      2001/10/15  tnksys@nitto-kohki.co.jp    //
// Changed history                                                              //
// 2001/10/15 Created  function.php                                             //
// 2002/09/02 Tnk Web site access_log function 追加                             //
// 2002/12/09 posgreSQL のレコード更新 用 関数追加 query_affected               //
// 2002/12/10 query_affected_trans トランザクション版を追加                     //
// 2003/02/12 指定クエリーの実行 getResult2() を追加 オリジナルの物は二重       //
//            インデックスだったので数値インデックスのみにした foreach()で使用  //
// 2003/02/14 Unique Data の照会専用クエリー getUniResult() を追加              //
// 2003/04/21 authorityUser() にテスト用の認証ロジック追加と一部訂正            //
// 2003/05/01 getResultWithField()をシステム管理用のＤＢ処理専用に変更          //
// 2003/05/31 getUniResTrs() トランザクション内での照会時に使用 $connectを外    //
// 2003/06/16 getResultWithField2()を一般用として追加 php_error に出力(通常)    //
// 2003/06/25 PHP-4.3.3RC1でsession_start()二重宣言 Notice 対応                 //
// 2003/07/03 CLI版とFunctionを共有するためrequire_once()を絶対指定に変更       //
// 2003/07/15 getResultWithField3()一般用をgetRowdata2(連想配列なし)で追加      //
// 2003/10/22 getResultTrs()トランザクション版を作成(２次元配列に結果を格納)    //
// 2003/10/23 getResWithFieldTrs()フィールド名付加のトランザクション版を追加    //
// 2003/12/19 社員メニュー用の function を emp_function.php へ移動              //
// 2003/12/19 DBを access_log → access_log2 へ変更 TIMESTAMP(6)を使用          //
// 2004/05/12 menu_OnOff() フレームメニューのOn/Off(表示・非表示)関数追加       //
// 2004/06/10 メニューヘッダーの下にUser_ID・名前の表示用関数追加 view_user()   //
// 2004/07/17 MenuHeader.php クラスのrequire追加→ＮＧ(指定してはいけない)      //
// 2005/01/17 view_file_name(__FILE__)の関数を追加 require又はincludeのfile名   //
//            admin の場合substr($file, strlen($_SERVER['DOCUMENT_ROOT']), -4)  //
//            getResult()getResult2()でexecQuery内でのerrorを-1で返すように変更 //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため        //
// 2005/07/06 上記を共にconnectDB()を呼出すエイリアスする。DAO(pgsql.php)へ予定 //
// 2005/07/20 daoPsqlClass を作成したため DB関係のfunctionをDAOに合わせた       //
// 2005/11/24 authorityUser()内に埋め込まれていた管理用パスワードを暗号化       //
// 2006/09/28 access_log()にセッションの開始チェックを追加(OLD版もあったが改良) //
// 2006/10/04 getCheckAuthority() 共用(汎用)権限チェックfunctionを追加          //
// 2006/10/05 上記の権限チェックを変更 getCheckAuthority($division, $id='')     //
// 2007/01/16 getCheckAuthority()にcategory=4(権限レベル)の認証を追加           //
// 2007/04/23 function uriIndexCheck() を追加（詳しくは関数定義部を参照）       //
//////////////////////////////////////////////////////////////////////////////////

require_once ('/home/www/html/tnk-web/pgsql.php');
require_once ('/home/www/html/tnk-web/define.php');
// require_once ('/home/www/html/tnk-web/MenuHeader.php');

/* 接続用インターフェース */
function funcConnect()
{
    return connectDB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWD);
}

/* DAO(pgsql.php)接続用インターフェース → funcConnectへのインターフェースへ 旧スクリプトのために残す */
/* はずであったが実際にはこちらを使用した方が分かり易い名前のため中身を同じにしエイリアスとする */
function db_connect()
{
    // $conn_str = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
    // return pg_pConnect($conn_str);   // 持続的接続
    // return funcConnect();
    return connectDB(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWD);
}

/* レコード更新専用クエリーの実行(更新レコード数を返す)トランザクション版 */
function query_affected_trans($connect, $query)
{
    if (($res = pg_query($connect, $query)) != FALSE) {  // 更新できたか？
        return pg_affected_rows($res);              // return = 0 更新失敗
    } else {
        return -1;                                  // return = -1 DB 接続失敗
    }
}

/* 指定クエリーの実行 Unique (照会専用で戻り値は一つ) トランザクション版 */
function getUniResTrs($connect, $query, &$result)
{
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {      // レコードがあるか
            $result = pg_fetch_result($resource, 0, 0);  // データセット row=0番目, field=0番
            return 1;   // 検索成功
        }
        return $rows;   // 0=検索値なし -1=エラー
    }
    return -2;  // pg_query error
}

/* 指定クエリーの実行 $result は数値インデックスのみ トランザクション版 (getUniResTrsの改良) */
function getResultTrs($connect, $query, &$result)
{
    $result = array();      // 初期化
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {         // レコードがあるか
            for ($r=0; $r<$rows; $r++) {
                $result[$r] = pg_fetch_row($resource, $r);
            }
            return $rows;   // 検索成功(レコード数)
        }
        return $rows;   // 0=検索値なし -1=エラー
    }
    return -2;  // pg_query error
}

/* 指定クエリーの実行 フィールド名の配列付 $result は数値インデックスのみ トランザクション版 */
function getResWithFieldTrs($connect, $query, &$field, &$result)
{
    $field = array(); $result = array();    // 初期化
    if (($resource = pg_query($connect, $query)) !== FALSE) {
        if (($rows = pg_num_rows($resource)) > 0) {         // レコードがあるか
            $fields = pg_num_fields($resource);             // field 数をセット エラー時は-1を返す
            for ($f=0; $f<$fields; $f++) {
                $field[$f] = pg_field_name($resource, $f);  // フィールド名取得
            }
            for ($r=0; $r<$rows; $r++) {
                $result[$r] = pg_fetch_row($resource, $r);
            }
            return $rows;                           // 検索成功(レコード数)
        }
        return $rows;   // 0=検索値なし -1=エラー
    }
    return -2;  // pg_query error
}


/* 指定クエリーの実行 Unique (照会専用で戻り値は一つ) */
function getUniResult($query, &$result)
{
    if ($conn = funcConnect()) {                        // 持続的接続
        if (($res = pg_query($conn, $query)) !== FALSE) {
            if (($rows = pg_num_rows($res)) > 0) {      // レコードがあるか
                $result = pg_fetch_result($res, 0, 0);  // データセット row=0番目, field=0番
                return 1;                               // 検索成功
            }
            return $rows;   // 検索値なし $rows = 0 ← php5.0.4では -1が返る
        }
        return -2;  // pg_query error
    }
    return -3;  // 接続できない
}

/* レコード更新専用クエリーの実行(更新レコード数を返す) */
function query_affected($query)
{
    if ( $conn = funcConnect() ) {          // 持続的接続
        $res = pg_query($conn, $query);
        return pg_affected_rows($res);      // return = 0 更新失敗
    } else {
        return -1;                          // return = -1 DB 接続失敗
    }
}

/* 指定クエリーの実行 数値インデックス + フィールド名の連想インデックスの二重インデックス 2003/02/12のコメント */
function getResult($sql, &$result)
{
    if ($conn=funcConnect()) {
        $result = array();      // 初期化
        if (($resource = pg_query($conn, $sql)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {         // レコードがあるか
                for ($i=0; $i<$rows; $i++) {
                    $result[$i] = pg_fetch_array($resource, $i);
                }
            }
            return $rows;   // 0レコード以上は成功 -1=pg_num_rows error
        }
        return -2;  // pg_query error
    }
    return -3;  // 接続できない
}

/* 指定クエリーの実行 $result は数値インデックスのみ foreach() で使用 2003/02/12 */
function getResult2($sql, &$result)
{
    if ($conn=funcConnect()) {
        $result = array();      // 初期化
        if (($resource = pg_query($conn, $sql)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {         // レコードがあるか
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_row($resource, $r);
                }
                return $rows;   // 検索成功(レコード数)
            }
            return $rows;   // 0=検索値なし ← php5.0.4では -1が返る -1=エラー
        }
        return -2;  // pg_query error
    }
    return -3;              // 接続できない
}
    /* 以下は getResult2() の使用例です */
/******************************************************************
if (($rows=getResult2($query, $res)) > 0) {
    $r = 0;
    $c = 0;
    foreach ($res as $res2) {
        foreach ($res2 as $value) {
            printf("r=%d:c=%d::%d <br>\n", $r, $c, $value);
            $c++;
        }
        $r++;
        $c = 0;
    }
}
******************************************************************/


/***** システム管理用のＤＢ処理専用 エラーメッセージは画面のみ *****/
function getResultWithField($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // 初期化
        if (($resource = @pg_query($connect, $query)) !== FALSE) {  // @ でエラーメッセージ抑止
            if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                $fields = pg_num_fields($resource);             // field 数をセット エラー時は-1を返す
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // フィールド名取得
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_array($resource, $r);    // 数値＋連想インデックス
                }
                return $rows;                           // 検索成功(レコード数)
            }
            return $rows;   // 0=検索値なし -1=エラー
        }
        echo "<tr><td>\n";
        if (isset($php_errormsg)) {
            echo "<div style='color: #ff1e00;'>{$php_errormsg}</div>\n";      // ブラウザー出力
        } else {
            echo "<div style='color: #ff1e00;'>php.ini の track_errors = Off を On にして下さい！</div>\n";      // ブラウザー出力
        }
        echo "</td></tr>\n";
        return -2;  // pg_query error
    }
    return -3;  // 接続できない
}

/***** 一般用ＤＢ処理 エラーメッセージは php_error に出力 *****/
function getResultWithField2($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // 初期化
        if (($resource = pg_query($connect, $query)) !== FALSE) {   // エラーはphp_errorへ
            if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                $fields = pg_num_fields($resource);             // field 数をセット エラー時は-1を返す
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // フィールド名取得
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_array($resource, $r);    // 数値＋連想インデックス
                }
                return $rows;                           // 検索成功(レコード数)
            }
            return $rows;   // 0=検索値なし -1=エラー
        }
        return -2;  // pg_query error
    }
    return -3;  // 接続できない
}

/***** 一般用ＤＢ処理 エラーメッセージは php_error に出力 *****/
///////// $result は数値インデックスのみ
function getResultWithField3($query, &$field, &$result)
{
    if ($connect=funcConnect()) {
        $field = array(); $result = array();    // 初期化
        if (($resource = pg_query($connect, $query)) !== FALSE) {   // エラーはphp_errorへ
            if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                $fields = pg_num_fields($resource);             // field 数をセット エラー時は-1を返す
                for ($f=0; $f<$fields; $f++) {
                    $field[$f] = pg_field_name($resource, $f);      // フィールド名取得
                }
                for ($r=0; $r<$rows; $r++) {
                    $result[$r] = pg_fetch_row($resource, $r);    // 数値インデックスのみ
                }
                return $rows;                           // 検索成功(レコード数)
            }
            return $rows;   // 0=検索値なし -1=エラー
        }
        return -2;  // pg_query error
    }
    return -3;  // 接続できない
}


/* ユーザー認証 */
function authorityUser($userid, $passwd, &$authority)
{
    if (strlen($passwd) != 32) $passwd = md5($passwd);
    /*** 2003/04/21 テスト用に追加 ***/
    if (($userid >= 0) && ($userid <= 9) && ($passwd == 'efd9d4fee1dc7684f7699bd7e7f11f67')) {
        $authority = $userid;
        return true;
    }
    /*** 2003/04/21 End ***/
    if ( funcConnect() ) {
        $query = "select retire_date from user_detailes where uid='$userid'";
        if ( execQuery($query) ) {
            $rowdata = array();
            if ( getRowdata(0,$rowdata) ) {
                if ($rowdata['retire_date'] != NULL) {
                    disConnectDB();
                    return false;
                }
            }
        }
        $query = "select * from user_master where uid='$userid' and passwd='$passwd'";
        if ( execQuery($query) ) {
            $rowdata = array();
            if ( getRowdata(0,$rowdata) ) {
                $authority = $rowdata['aid'];
                disConnectDB();             // disConnectDB と return true を { } 内に移動 2003/04/21
                return true;
            }
        }
    }
    disConnectDB();
    return false;
}


//////////////////////////////////////////////////////////////////////////////
// Tnk Web Site 内のアクセスログ                                            //
// Copyright(C) 2002-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/04/02 Created access_log() function                                 //
// 2002/09/02 セッション管理 & register global off 対応                     //
// 2002/09/10 time_log を マイクロ秒まで保持するように変更maicrotime()      //
// 2002/09/28 $script_name を 可変長引数として扱うように変更                //
// 2003/06/25 PHP-4.3.3RC1でsession_start()二重宣言 Notice 対応             //
// 2003/12/19 DBを access_log → access_log2 へ変更 TIMESTAMP(6)を使用      //
// 2006/09/28 セッションの開始チェックを追加(OLD版にもあったが改良したもの) //
//////////////////////////////////////////////////////////////////////////////
function access_log()
{
    if (!isset($_SESSION)) {                    // セッションの開始チェック
        session_start();                        // Notice を避ける場合は頭に@(上記のif分があるため必要ない)
    }
    $addr_log = $_SERVER['REMOTE_ADDR'];
    $host_log = gethostbyaddr($addr_log);
    // $date_log = date('Y-m-d');
    // $time_log = date('H:i:s:');             // 2002/09/10 最後に':'を追加
    // $time_log .= substr(microtime(),2,2);   // 2002/09/10 マイクロ秒を追加
    
    if (func_num_args() >= 1) {
        if (func_get_arg(0) == '') {    // NULL → '' へ変更
            $file_log = $_SERVER['SCRIPT_NAME'];
        } else {
            $file_log = func_get_arg(0);
        }
    } else {
        $file_log = $_SERVER['SCRIPT_NAME'];
    }
    $con = funcConnect();
    if ($con) {
        execQuery('begin');
        if (isset($_SESSION['User_ID'])) {
            $query = "insert into access_log2 (ip_addr, host, uid, script)
                    values('{$addr_log}', '{$host_log}', '{$_SESSION['User_ID']}', '{$file_log}')";
        } else {
            $query = "insert into access_log2 (ip_addr, host, uid, script)
                    values('{$addr_log}', '{$host_log}', NULL, '{$file_log}')";
        }
        if (execQuery($query) >= 0) {
            execQuery('commit');
            disConnectDB();
        } else {
            execQuery('rollback');
            disConnectDB();
        }
    }
}


//////////////////////////////////////////////////////////////////////////////
// フレームメニューの On/Off(表示・非表示)関数                              //
//////////////////////////////////////////////////////////////////////////////
function menu_OnOff($script)
{
    /***** サイトメニュー On / Off *****/
    if ($_SESSION['site_view'] == 'on') {
        $site_view = 'MenuOFF';
    } else {
        $site_view = 'MenuON';
    }
                                                             // ret_border は各メニューで使用している
    return "
        <td width='40' align='center' valign='center' class='ret_border'>
            <input style='font-size:8.5pt; font-family:monospace;' type='submit' name='site' value='{$site_view}'
            onClick=\"top.location.href = '/menu_frame_OnOff.php?name={$script}';\">
        </td>
    ";
    // 子フレーム対応のため parent.location.href → top.location.href へ変更
    /*********************
    return "<td width='40' bgcolor='#d6d3ce' align='center' valign='center' class='ret_border'>
            <input style='font-size:8.5pt; font-family:monospace;' type='submit' name='site' value='{$site_view}'
            onClick=\"parent.location.href = '/menu_frame_OnOff.php?name={$script}';\">
            </td>\n";
    *********************/
}


//////////////////////////////////////////////////////////////////////////////
// ヘッダーのメニューバーの下にユーザーＩＤ・ユーザー名の表示               //
//////////////////////////////////////////////////////////////////////////////
function view_user($u_id)
{
    switch ($u_id) {
    case 0:
    case 1:
    case 2:
    case 3:
    case 4:
    case 5:
        $auth = (int)$u_id;     // 整数型に変換
        $name = "Auth{$auth}";  // 文字列で連結
        break;
    default:
        $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$u_id}'";
        if (getUniResult($query, $name) <= 0) {
            $name = 'check'; // 未登録又はエラーなら
        }
    }
    return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='right' style='font-size:10pt; font-weight:normal;'>
                {$u_id} {$name}
            </td>
        </table>
    ";
}

//////////////////////////////////////////////////////////////////////////////
// title_boder の下に include fileを表示(basenameのみ)                      //
// 使用方法：require又はincludeファイルの中でview_file_name(__FILE__)を指定 //
//////////////////////////////////////////////////////////////////////////////
function view_file_name($file = '')
{
    if ($file != '') {
        if ($_SESSION['Auth'] <= 2) {
            $name = basename($file, '.php');    // 上級ユーザー以下
        } else {
            $name = substr($file, strlen($_SERVER['DOCUMENT_ROOT']), -4);  // adminなら
        }
    } else {
        $name = '';
    }
    return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='right' style='font-size:10pt; font-weight:normal;'>
                {$name}
            </td>
        </table>
    ";
}


//////////////////////////////////////////////////////////////////////////////
// 共用(汎用)権限チェック ファンクッション  編集権限等の確認用に使用する    //
// Copyright(C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2006/10/04 Created function getCheckAuthority($id, $division)            //
//            $id = チェック対象ID(権限種別により動的) text                 //
//            $division = 権限種別 integer 1=社員番号, 2=IPアドレス, 3=部門 //
//            return boolean                                                //
// 2006/10/05 パラメーターを権限No.($division)のみに$idはオプション(その他) //
//            権限No.のメンバーに登録されているcategoryにより問合せの切替   //
// 2007/01/16 getCheckAuthority()にcategory=4(権限レベル)の認証を追加           //
//////////////////////////////////////////////////////////////////////////////
function getCheckAuthority($division, $id='')
{
    if ( ($division < 1) || ($division > 32000) ) return false;
    if (!isset($_SESSION['User_ID'])) return false;
    $con = db_connect();
    query_affected_trans($con, 'BEGIN');
    $query = "
        SELECT category FROM common_authority LEFT OUTER JOIN common_auth_category USING(id)
        WHERE division={$division} GROUP BY category ORDER BY category ASC
    ";
    $res = array();
    $rows = getResultTrs($con, $query, $res);
    for ($i=0; $i<$rows; $i++) {
        switch ($res[$i][0]) {
        case 1:     // 社員番号で問合せ
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$_SESSION['User_ID']}'";
            break;
        case 2:     // IPアドレスで問合せ
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$_SERVER['REMOTE_ADDR']}'";
            break;
        case 3:     // 部門コードで問合せ
            $query = "SELECT act_id FROM cd_table WHERE uid='{$_SESSION['User_ID']}'";
            $act_id = 0;    // 初期化
            getUniResTrs($con, $query, $act_id);
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$act_id}'";
            break;
        case 4:     // 権限レベルで問合せ (0=一般, 1=中級, 2=上級, 3=アドミニ)
            $query = "SELECT aid FROM user_master WHERE uid='{$_SESSION['User_ID']}'";
            $aid = -1;      // 初期化
            getUniResTrs($con, $query, $aid);                           // 権限レベルなので<=(以下)に注意
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id<='{$aid}'";
            break;
        default:    // その他は指定IDで問合せ
            $id = addslashes($id);  // ',",\,NULL のエスケープ 本来はpg_escape_string()を使用したいがPostgreSQLに依存するため避けた。
            $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$id}'";
        }
        if (getUniResTrs($con, $query, $regdate) > 0) {
            query_affected_trans($con, 'COMMIT');
            return true;
        }
    }
    query_affected_trans($con, 'COMMIT');
    return false;
}


//////////////////////////////////////////////////////////////////////////////
// 2007/04/23 URIに指定されたファイルがindex.phpかチェックして、そうであれば//
// ファイル名を省略した URIでリダイレクトさせる。それ以外は何もしない。     //
//////////////////////////////////////////////////////////////////////////////
function uriIndexCheck()
{
    if (basename($_SERVER['SCRIPT_NAME']) != 'index.php') return;
    if (basename($_SERVER['REQUEST_URI']) == basename($_SERVER['SCRIPT_NAME'])) {
        $uri = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI']);
        header('Location: ' . H_WEB_HOST . $uri);
        exit();
    }
    /*********** 以下は自分自身が直接指定された場合 あまり用途はないと思うが
    if (basename(__FILE__) != 'index.php') return;
    if (basename($_SERVER['REQUEST_URI']) == basename(__FILE__)) {
        $uri = str_replace(basename(__FILE__), '', $_SERVER['REQUEST_URI']);
        header('Location: ' . H_WEB_HOST . $uri);
        exit();
    }
    ***********/
}

?>
