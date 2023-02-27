<?php
//////////////////////////////////////////////////////////////////////////////
// プログラムマスターの照会・メンテ MVC Model 部                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_Model.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*       生産システムの部品・製品のアイテムマスター MVCのModel部の 拡張クラスの定義       *
*****************************************************************************************/
class ProgMaster_Model extends ComTableMnt
{
    ///// Private properties
    private $pidKey = '';                         // キーフィールド
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $pidKey='')
    {
        if ($pidKey == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $this->pidKey = $pidKey;    // Propertiesへの登録
        }
        $sql_sum = "
            SELECT count(*) FROM program_master where p_id like '{$pidKey}%'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'progMaster_input_Master.log');
    }
    
    ////////// マスター追加
    public function table_add($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql1 = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // pidの登録済みのチェック
                $_SESSION['s_sysmsg'] = "プログラム名：{$pid} ディレクトリ：{$pdir} は既に登録されています！";
                return false;
            } //else {
                //$response = $this->add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
                //if ($response) {
                //    return true;
                //} else {
                //    $_SESSION['s_sysmsg'] = '登録できませんでした。';
                //}
            //}
            if ($db1 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db1}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db1} は存在しません";
                    return false;
                }
            }
            if ($db2 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db2}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db2} は存在しません";
                    return false;
                }
            }
            if ($db3 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db3}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db3} は存在しません";
                    return false;
                }
            }
            if ($db4 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db4}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db4} は存在しません";
                    return false;
                }
            }
            if ($db5 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db5}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db5} は存在しません";
                    return false;
                }
            }
            if ($db6 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db6}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db6} は存在しません";
                    return false;
                }
            }
            if ($db7 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db7}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db7} は存在しません";
                    return false;
                }
            }
            if ($db8 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db8}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db8} は存在しません";
                    return false;
                }
            }
            if ($db9 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db9}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db9} は存在しません";
                    return false;
                }
            }
            if ($db10 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db10}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db10} は存在しません";
                    return false;
                }
            }
            if ($db11 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db11}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db11} は存在しません";
                    return false;
                }
            }
            if ($db12 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db12}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                    $_SESSION['s_sysmsg'] = "データベース：{$db12} は存在しません";
                    return false;
                }
            }
            $response = $this->add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '登録できませんでした。';
            }
        } else {
            $_SESSION['s_sysmsg'] = 'プログラムのマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスター 変更
    public function table_change($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $query = "select p_id from program_master where p_id='{$prePid}' AND dir='{$preDir}'";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前の部品番号が登録されているか？
                $chk_sql1 = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}'";
                if (($prePid != $pid) || ($preDir != $pdir)) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // 変更後の部品番号が既に登録されているか？
                        $_SESSION['s_sysmsg'] = "プログラム名：{$pid} ディレクトリ：{$pdir} は既に登録されています！";
                        return false;
                    }
                }
                // データベースの存在チェック
                if ($db1 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db1}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db1} は存在しません";
                        return false;
                    }
                }
                if ($db2 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db2}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db2} は存在しません";
                        return false;
                    }
                }
                if ($db3 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db3}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db3} は存在しません";
                        return false;
                    }
                }
                if ($db4 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db4}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db4} は存在しません";
                        return false;
                    }
                }
                if ($db5 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db5}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db5} は存在しません";
                        return false;
                    }
                }
                if ($db6 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db6}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db6} は存在しません";
                        return false;
                    }
                }
                if ($db7 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db7}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db7} は存在しません";
                        return false;
                    }
                }
                if ($db8 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db8}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db8} は存在しません";
                        return false;
                    }
                }
                if ($db9 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db9}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db9} は存在しません";
                        return false;
                    }
                }
                if ($db10 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db10}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db10} は存在しません";
                        return false;
                    }
                }
                if ($db11 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db11}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db11} は存在しません";
                        return false;
                    }
                }
                if ($db12 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db12}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // データベースの存在チェック
                        $_SESSION['s_sysmsg'] = "データベース：{$db12} は存在しません";
                        return false;
                    }
                }
                $response = $this->chg_execute($prePid, $pid, $pname, $pdir, $preDir,$pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '変更できませんでした。';
                }
            } else {
                $_SESSION['s_sysmsg'] = "プログラム名：{$prePid} ディレクトリ：{$preDir} は他の人に変更されました！{$pid}";
            }
        } else {
            $_SESSION['s_sysmsg'] = 'プログラムのマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($pid, $pdir)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // pidの存在チェック
                $_SESSION['s_sysmsg'] = "プログラム名：{$pid} ディレクトリ：{$pdir} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($pid, $pdir);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '削除できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = 'プログラムのマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部
    public function getViewDataList(&$result)
    {
        ///// 常に $pidKey フィールドでの検索
        $query = "
            SELECT p_id         AS プログラムID
                ,p_name         AS プログラム名
                ,dir            AS ディレクトリ
                ,comment        AS コメント
                ,db1            AS DB使用1
                ,db2            AS DB使用2
                ,db3            AS DB使用3
                ,db4            AS DB使用4
                ,db5            AS DB使用5
                ,db6            AS DB使用6
                ,db7            AS DB使用7
                ,db8            AS DB使用8
                ,db9            AS DB使用9
                ,db10           AS DB使用10
                ,db11           AS DB使用11
                ,db12           AS DB使用12
                ,last_date      AS 登録日時
            FROM
                program_master
            WHERE
                p_id like '{$this->pidKey}%'
            ORDER BY
                dir ASC, p_id ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($pid, $pdir, &$result)
    {
        $query = "
            SELECT p_id
                ,p_name
                ,dir
                ,comment
                ,db1
                ,db2
                ,db3
                ,db4
                ,db5
                ,db6
                ,db7
                ,db8
                ,db9
                ,db10
                ,db11
                ,db12
                ,last_date
            FROM
                program_master
            WHERE
                p_id = '{$pid}' AND dir = '{$pdir}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('pname', $res[0][1]);
            $result->add_once('pcomment',$res[0][3]);
            $result->add_once('db1', $res[0][4]);
            $result->add_once('db2', $res[0][5]);
            $result->add_once('db3', $res[0][6]);
            $result->add_once('db4', $res[0][7]);
            $result->add_once('db5', $res[0][8]);
            $result->add_once('db6', $res[0][9]);
            $result->add_once('db7', $res[0][10]);
            $result->add_once('db8', $res[0][11]);
            $result->add_once('db9', $res[0][12]);
            $result->add_once('db10', $res[0][13]);
            $result->add_once('db11', $res[0][14]);
            $result->add_once('db12', $res[0][15]);
            $result->add_once('last_date', $res[0][16]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// 生産関係のマスター編集権限メソッド(後日、共用メソッド移行する)
    protected function IndustAuthUser($class)
    {
        // $class は将来的に使用予定 (MASTER/PLAN/ORDER/...)
        $LoginUser = $_SESSION['User_ID'];
        $query = "select sid from user_detailes where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            if ($sid == 21) {   // 生産管理課ならOK
                return true;
            } elseif ($_SESSION['Auth'] >= 3) { // テスト用
                return true;
            }
            return false;
        } else {
            return false;
        }
    }
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 設備･機械のインターフェース マスター 追加
    private function add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録は miitemにはない
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into program_master
            (p_id, p_name, dir, comment, db1, db2, db3, db4, db5, db6, db7, db8, db9, db10, db11, db12,last_date, last_user)
            values
            ('$pid', '$pname', '$pdir', '$pcomment', '$db1', '$db2', '$db3', '$db4', '$db5', '$db6', '$db7', '$db8', '$db9', '$db10', '$db11', '$db12','$last_date', '$last_user')
        ";
        $this->log_openCheck('progMaster_input_Master.log');
        $this->set_page_rec(20);              // 初期化(順番が重要)
        return $this->execute_Insert($insert_qry, 'progMaster_input_Master.log');
    }
    
    ////////// 設備･機械のインターフェース マスター 変更
    private function chg_execute($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        // 保存用のSQL文を設定
        $save_sql = "select * from program_master where p_id='{$prePid}' AND dir='{$preDir}'";
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE program_master SET
            p_id='{$pid}', p_name='{$pname}', dir='{$pdir}', comment='{$pcomment}', db1='{$db1}', db2='{$db2}', db3='{$db3}', db4='{$db4}', db5='{$db5}', db6='{$db6}', db7='{$db7}', db8='{$db8}', db9='{$db9}', db10='{$db10}', db11='{$db11}', db12='{$db12}',last_date='{$last_date}', last_user='{$last_user}'
            WHERE p_id='{$prePid}' AND dir='{$preDir}'
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械のインターフェース マスター 削除(完全)
    private function del_execute($pid, $pdir)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from program_master where p_id='{$pid}' AND dir='{$pdir}'";
        $delete_sql = "delete from program_master where p_id='{$pid}' AND dir='{$pdir}'";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
} // Class EquipMacMstMnt_Model End

?>
