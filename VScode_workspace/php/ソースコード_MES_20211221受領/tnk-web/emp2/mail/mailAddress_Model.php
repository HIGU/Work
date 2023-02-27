<?php
//////////////////////////////////////////////////////////////////////////////
// 社員マスターのメールアドレス 照会・メンテナンス                          //
//                                                            MVC Model 部  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Model.php                               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../ComTableMntClass.php');   // TNK 全共通 テーブルメンテ&ページ制御Class


/******************************************************************************
*     打合せ(会議)スケジュール用 MVCのModel部 base class 基底クラスの定義     *
******************************************************************************/
class mailAddress_Model extends ComTableMnt
{
    ///// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        switch ($request->get('condition')) {
        case 'taishoku':
            $this->where = "WHERE uid != '000000' AND retire_date IS NOT NULL";
            break;
        case 'syukko':
            $this->where = "WHERE uid != '000000' AND sid = 31 AND retire_date IS NULL";
            break;
        case 'ALL':
            $this->where = "WHERE uid != '000000'";
            break;
        case 'genzai':
        default:
            $this->where = "WHERE uid != '000000' AND retire_date IS NULL AND sid != 31";
            break;
        }
        $sql_sum = "
            SELECT count(*) FROM user_master LEFT OUTER JOIN user_detailes USING(uid) {$this->where}
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'mail/mailAddress.log', 15);    // 1頁レコード数の初期値=15を指定
    }
    
    ////////// メールアドレスの登録・変更 (実際にはここで登録はしない)
    public function mail_edit($uid, $mailaddr)
    {
        ///// 編集権限のチェック
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uidの適正チェック
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT uid, mailaddr FROM user_master WHERE uid='{$uid}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '現在このメニューでアドレスの登録は禁止しています。';
            return false;
            // アドレスの登録
            $response = $this->mailInsert($uid, $mailaddr);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレス [ {$mailaddr} ] を登録しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレスの登録が出来ませんでした！";
            }
        } else {
            // アドレスの変更
            // データが変更されているかチェック
            if ($uid == $res[0][0] && $mailaddr == $res[0][1]) return true;
            // アドレスの変更 実行
            $response = $this->mailUpdate($uid, $mailaddr);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレスを [ {$mailaddr} ] に変更しました。";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレスの変更が出来ませんでした！";
            }
        }
        return false;
    }
    
    ////////// 会議室の 削除
    public function mail_omit($uid, $mailaddr)
    {
        ///// 編集権限のチェック
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uidの適正チェック
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT uid, mailaddr FROM user_master WHERE uid='{$uid}'
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[ {$name} ] さんの [{$uid}] {$mailaddr} は削除対象データがありません！";
        } else {
            ///// 削除しても問題ないか過去のデータをチェック(今回はuser_detailesでチェック)
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid='{$uid}';
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->mailDelete($uid);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレス [ {$mailaddr} ] を削除しました。";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[ {$name} ] さんのメールアドレス {$mailaddr} を削除出来ませんでした！";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$uid}] {$mailaddr} は現在使用中です。削除できません！";
            }
        }
        return false;
    }
    
    ////////// メールアドレスの 有効・無効 (今回このメソッドは使用しない)
    public function mail_activeSwitch($uid, $mailaddr)
    {
        ///// 編集権限のチェック
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uidの適正チェック
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT active FROM user_master WHERE uid='{$uid}'
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$uid}] {$mailaddr} の対象データがありません！";
        } else {
            // ここに last_date last_host の登録処理を入れる
            // regdate=自動登録
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            // 保存用のSQL文を設定
            $save_sql = "
                SELECT active FROM user_master WHERE uid='{$uid}'
            ";
            $update_sql = "
                UPDATE user_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE uid='{$uid}'
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部
    public function getViewMailList(&$result)
    {
        $query = "
            SELECT master.uid                           -- 00
                ,trim(name)                             -- 01
                ,trim(master.mailaddr)                  -- 02
                ,CASE
                    WHEN master.last_date IS NULL THEN '01/07/01 08:30'
                    ELSE to_char(master.last_date, 'YY/MM/DD HH24:MI')
                 END                                    -- 03
            FROM
                user_master AS master
            LEFT OUTER JOIN
                user_detailes USING(uid)
            {$this->where}
            ORDER BY
                pid DESC, sid ASC, master.uid ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 編集権限をチェックして結果を返す (false=照会のみ, true=編集OK)
    protected function checkAuth()
    {
        ///// Authの適正チェック
        if ($_SESSION['Auth'] >= 2) {
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '編集権限がありません！ 照会のみ行って下さい。';
            return false;
        }
    }
    
    ////////// メールアドレスのuidの適正をチェックしメッセージ＋結果(true=名前,false=NG)を返す
    protected function checkUid($uid)
    {
        ///// uidの適正チェック (user_detailesに登録があるか)
        $query = "
            SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}'
        ";
        if ($this->getUniResult($query, $name) > 0) {
            return $name;
        } else {
            $_SESSION['s_sysmsg'] = "[ {$uid} ] は無効な社員番号です。";
        }
        return false;
    }
    
    ////////// メールアドレスの登録 (実行部) (今回は使用しない)
    protected function mailInsert($uid, $mailaddr)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        // $last_date = date('Y-m-d H:i:s');
        // $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $insert_sql = "
            INSERT INTO user_master
            (uid, mailaddr, last_date, last_host)
            VALUES
            ('$uid', '$mailaddr', '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// メールアドレスの変更 (実行部)
    protected function mailUpdate($uid, $mailaddr)
    {
        // ここに last_date last_host の登録処理を入れる
        // regdate=自動登録
        // $last_date = date('Y-m-d H:i:s');
        // $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // 保存用のSQL文を設定
        $save_sql = "
            SELECT uid, mailaddr, last_date, last_user FROM user_master WHERE uid='{$uid}'
        ";
        $update_sql = "
            UPDATE user_master SET
            mailaddr='{$mailaddr}'
            WHERE uid='{$uid}'
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// メールアドレスの削除 (実行部)
    protected function mailDelete($uid)
    {
        // 保存用のSQL文を設定
        $save_sql   = "
            SELECT * FROM user_master WHERE uid='{$uid}'
        ";
        // 削除用SQL文を設定
        $delete_sql = "
            DELETE FROM user_master WHERE uid='{$uid}'
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
} // Class mailAddress_Model End

?>
