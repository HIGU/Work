<?php
//////////////////////////////////////////////////////////////////////////////
// DAO (Data Access Object) PostgreSQL DB インターフェースの実装            //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/20 Created   daoPsqlClass.php                                    //
// 2005/07/22 file名を daoPsqlClass.php → daoInterfaceClass.php            //
//   Ver1.00                        RDBMSの変更をこのクラスで吸収するため   //
// 2006/07/13 新規 共通権限チェックメソッド getCheckAuthority(ID, Division) //
//   Ver1.10    ID=権限ID(社員番号やIPアドレス等), Division=権限区分 を追加 //
// 2006/10/04 上記のgetCheckAuthority()メソッドを一部ロジック変更           //
//   Ver1.11  ini_set('error_reporting', E_ALL) をコメント 呼出元で設定する //
// 2006/10/05 getCheckAuthority($id, $division) →                          //
//   Ver1.12           getCheckAuthority($division, $id='') $idはオプション //
// 2007/01/16 getCheckAuthority()にcategory=4(権限レベル)の認証を追加       //
//   Ver----    DAO_VERSION の変更は無し                                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用

require_once ('define.php');                // DB Connection データを読込む
require_once ('daoInterface.php');          // DAO Interface を読込む

if (class_exists('daoInterfaceClass')) {
    return;
}
if (DAO_VERSION !== '1.12') {
    return;
}

/****************************************************************************
*                       base class 基底クラスの定義                         *
****************************************************************************/
///// namespace Common {} は現在使用しない 使用例：Common::ComTableMnt → $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (名前空間::リテラル)
class daoInterfaceClass implements daoInterface
{
    /////////////////////////////////////////////////////////////////////////
    /*        DB コネクション return=コネクションリソース FASE(error)      */
    /////////////////////////////////////////////////////////////////////////
    public function connectDB()
    {
        if (DB_HOST == 'local') {
            $connstr = 'dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        } else {
            $connstr = 'host='.DB_HOST.' port='.DB_PORT.' dbname='.DB_NAME.' user='.DB_USER.' password='.DB_PASSWD;
        }
        return pg_pConnect($connstr);   // 持続的接続
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* ユニークなデータを取出す return=TRUE/FALSE                          */
    /////////////////////////////////////////////////////////////////////////
    public function getUniResult($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // 持続的接続
            if (($res = pg_query($conn, $sql)) !== FALSE) {
                if (($rows = pg_num_rows($res)) > 0) {      // レコードがあるか
                    $result = pg_fetch_result($res, 0, 0);  // データセット row=0番目, field=0番
                    return TRUE;                            // 検索成功
                }
                return FALSE;   // 検索値なし $rows = 0 ← php5.0.4では -1が返る -1=pg_num_rows error
            }
            return FALSE;   // pg_query error
        }
        return FALSE;   // 接続失敗
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* 表形式なデータを取出す$result[$r][$f](数値indexのみ) return=rows    */
    /////////////////////////////////////////////////////////////////////////
    public function getResult2($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // 持続的接続
            $result = array();      // 初期化
            if (($resource = pg_query($conn, $sql)) !== FALSE) {
                if (($rows = pg_num_rows($resource)) > 0) {         // レコードがあるか
                    for ($r=0; $r<$rows; $r++) {
                        $result[$r] = pg_fetch_row($resource, $r);
                    }
                }
                return $rows;   // 0レコード以上は成功 -1=pg_num_rows error
            }
            return -2;  // pg_query error
        }
        return -3;  // 接続できない
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* 表形式なデータ(数値インデックス+連想配列)を取出す return=rows       */
    /////////////////////////////////////////////////////////////////////////
    /********** 互換性のために残す **********/
    public function getResult($sql, &$result)
    {
        if ($conn = $this->connectDB()) {                   // 持続的接続
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
    
    /////////////////////////////////////////////////////////////////////////
    /*          トランザクション用 SQL実行 return=更新数 <0(error)         */
    /////////////////////////////////////////////////////////////////////////
    public function query_affected_trans($connect, $sql)
    {
        if (($res = pg_query($connect, $sql)) !== FALSE) {   // 更新できたか？
            return pg_affected_rows($res);  // return = 0 更新対象なし又は失敗
        } else {
            return -1;  // pg_query error
        }
    }
    
    //////////////////////////////////////////////////////////////////////////
    /*          １ショット更新用 SQL実行 return=更新数 <0(error)            */
    //////////////////////////////////////////////////////////////////////////
    public function query_affected($sql)
    {
        if ($conn = $this->connectDB()) {                   // 持続的接続
            if (($res = pg_query($conn, $sql)) !== FALSE) { // 更新できたか？
                return pg_affected_rows($res);              // return = 0 更新対象なし又は失敗
            }
            return -1;  // pg_query error
        }
        return -2;  // 接続できない
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* トランザクション用 Unique 照会専用で戻り値は一つ                    */
    /*      return=1(検索値あり) 0(検索値なし) <0(error)                   */
    /////////////////////////////////////////////////////////////////////////
    public function getUniResTrs($connect, $query, &$result)
    {
        if (($resource = pg_query($connect, $query)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) > 0) {      // レコードがあるか
                $result = pg_fetch_result($resource, 0, 0);  // データセット row=0番目, field=0番
                return 1;   // 検索成功
            }
            return $rows;   // 0=検索値なし ← php5.0.4では -1が返る
        }
        return -2;  // pg_query error
    }
    
    /////////////////////////////////////////////////////////////////////////
    /* トランザクション用 表形式データを取出す$result[$r][$f](数値index)   */
    /*      return= rows>0(検索値あり) 0(検索値なし) <0(error)             */
    /////////////////////////////////////////////////////////////////////////
    public function getResultTrs($connect, $query, &$result)
    {
        $result = array();      // 初期化
        if (($resource = pg_query($connect, $query)) !== FALSE) {
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
    
    //////////////////////////////////////////////////////////////////////////
    /* トランザクション用 表形式データを取出す$result[$r][$f](数値index)    */
    /* + field名の配列 return= rows>0(検索値あり) 0(検索値なし) <0(error)   */
    /* 0レコードでもフィールドだけ取出せる 0 record対応                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResWithFieldTrs($connect, $query, &$field, &$result)
    {
        $field = array(); $result = array();    // 初期化
        if (($resource = pg_query($connect, $query)) !== FALSE) {
            if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
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
    
    //////////////////////////////////////////////////////////////////////////
    /* システム管理用のＤＢ処理専用 エラーメッセージは画面のみ １ショット   */
    /* + field名の配列 return= rows>0(検索値あり) 0(検索値なし) <0(error)   */
    /* 0レコードでもフィールドだけ取出せる 0 record対応                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // 持続的接続
            $field = array(); $result = array();    // 初期化
            if (($resource = @pg_query($connect, $query)) !== FALSE) {  // @ でエラーメッセージ抑止
                if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                    $fields = pg_num_fields($resource);                 // field 数をセット エラー時は-1を返す
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
    
    //////////////////////////////////////////////////////////////////////////
    /*  一般用ＤＢ処理 php_error出力 １ショット版 数値index＋連想index      */
    /* + field名の配列 return= rows>0(検索値あり) 0(検索値なし) <0(error)   */
    /* 0レコードでもフィールドだけ取出せる 0 record対応                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField2($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // 持続的接続
            $field = array(); $result = array();    // 初期化
            if (($resource = pg_query($connect, $query)) !== FALSE) {   // エラーはphp_errorへ
                if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                    $fields = pg_num_fields($resource);                 // field 数をセット エラー時は-1を返す
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
    
    //////////////////////////////////////////////////////////////////////////
    /* 一般用ＤＢ処理 php_error出力 １ショット版 $resultは数値indexのみ     */
    /* + field名の配列 return= rows>0(検索値あり) 0(検索値なし) <0(error)   */
    /* 0レコードでもフィールドだけ取出せる 0 record対応                     */
    //////////////////////////////////////////////////////////////////////////
    public function getResultWithField3($query, &$field, &$result)
    {
        if ($connect = $this->connectDB()) {    // 持続的接続
            $field = array(); $result = array();    // 初期化
            if (($resource = pg_query($connect, $query)) !== FALSE) {   // エラーはphp_errorへ
                if (($rows = pg_num_rows($resource)) >= 0) {    // レコード0でもfieldだけ使うため>=0にしている
                    $fields = pg_num_fields($resource);                 // field 数をセット エラー時は-1を返す
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
    
    //////////////////////////////////////////////////////////////////////////
    /* 共通権限有り無し取得メソッド IDに対して権限のありなし                */
    /* id=string型, divisionは権限区分1～順番にインクリメントinteger型      */
    /* 戻り値は bool型 true=権限あり, false=権限無し                        */
    /*          public method   getCheckAuthority($id, $division)           */
    /* $id = チェック対象ID(権限種別により動的) text                        */
    /* $division = 権限種別 integer 1=社員番号, 2=IPアドレス, 3=部門        */
    /* return boolean                                                       */
    /* Ver1.12 パラメーターを権限No.($division)のみに$idはオプション(その他)*/
    /*          権限No.のメンバーに登録されているcategoryにより問合せの切替 */
    /* 2007/01/16 getCheckAuthority()にcategory=4(権限レベル)の認証を追加   */
    //////////////////////////////////////////////////////////////////////////
    public function getCheckAuthority($division, $id='')
    {
        if ( ($division < 1) || ($division > 32000) ) return false;
        if (!isset($_SESSION['User_ID'])) return false;
        $con = $this->connectDB();
        $this->query_affected_trans($con, 'BEGIN');
        $query = "
            SELECT category FROM common_authority LEFT OUTER JOIN common_auth_category USING(id)
            WHERE division={$division} GROUP BY category ORDER BY category ASC
        ";
        $res = array();
        $rows = $this->getResultTrs($con, $query, $res);
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
                $this->getUniResTrs($con, $query, $act_id);
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$act_id}'";
                break;
            case 4:     // 権限レベルで問合せ (0=一般, 1=中級, 2=上級, 3=アドミニ)
                $query = "SELECT aid FROM user_master WHERE uid='{$_SESSION['User_ID']}'";
                $aid = -1;      // 初期化
                $this->getUniResTrs($con, $query, $aid);                    // 権限レベルなので<=(以下)に注意
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id<='{$aid}'";
                break;
            default:    // その他は指定IDで問合せ
                $id = addslashes($id);  // ',",\,NULL のエスケープ 本来はpg_escape_string()を使用したいがPostgreSQLに依存するため避けた。
                $query = "SELECT regdate FROM common_authority WHERE division={$division} AND id='{$id}'";
            }
            if ($this->getUniResTrs($con, $query, $regdate) > 0) {
                $this->query_affected_trans($con, 'COMMIT');
                return true;
            }
        }
        $this->query_affected_trans($con, 'COMMIT');
        return false;
    }
    
} // Class daoInterfaceClass End

?>
