<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスターの照会・メンテ MVC Model 部//
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Model.php                                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class


/*****************************************************************************************
*       生産システムの部品・製品のアイテムマスター MVCのModel部の 拡張クラスの定義       *
*****************************************************************************************/
class PartsItem_Model extends ComTableMnt
{
    ///// Private properties
    private $partsKey = '';                         // キーフィールド
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request, $partsKey='')
    {
        if ($partsKey == '') {
            return;    // キーフィールドが設定されていなければ何もしない
        } else {
            $this->partsKey = $partsKey;    // Propertiesへの登録
        }
        $sql_sum = "
            SELECT count(*) FROM miitem where mipn like '{$partsKey}%'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'parts_item_Master.log');
    }
    
    ////////// マスター追加
    public function table_add($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg='')
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql1 = "select mipn from miitem where mipn='{$parts_no}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // parts_noの登録済みのチェック
                $_SESSION['s_sysmsg'] = "部品・製品 番号：{$parts_no} は既に登録されています";
            } else {
                if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                $response = $this->add_execute($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '登録できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '生産関係のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスター 変更
    public function table_change($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg='')
    {
        if ($this->IndustAuthUser('MASTER')) {
            $query = "select mipn from miitem where mipn='{$preParts_no}'";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前の部品番号が登録されているか？
                $chk_sql1 = "select mipn from miitem where mipn='{$parts_no}'";
                if ($preParts_no != $parts_no) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // 変更後の部品番号が既に登録されているか？
                        $_SESSION['s_sysmsg'] = "部品・製品 番号：{$parts_no} は既に登録されています！";
                    } else {
                        if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                        $response = $this->chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                } else {
                    if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                    $response = $this->chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '変更できませんでした。';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "部品・製品 番号：{$preParts_no} は他の人に変更されました！";
            }
        } else {
            $_SESSION['s_sysmsg'] = '生産関係のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($parts_no)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql = "select mipn from miitem where mipn='{$parts_no}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // parts_noの存在チェック
                $_SESSION['s_sysmsg'] = "部品・製品 番号：{$parts_no} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($parts_no);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '削除できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '生産関係のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部
    public function getViewDataList(&$result)
    {
        ///// 常に $partsKey フィールドでの検索
        $query = "
            SELECT mipn         AS parts_no
                ,midsc          AS 名称
                ,CASE
                    WHEN mzist='' THEN '&nbsp;'
                    WHEN mzist IS NULL THEN '&nbsp;'
                    ELSE mzist
                 END            AS 材質
                ,CASE
                    WHEN mepnt='' THEN '&nbsp;'
                    WHEN mepnt IS NULL THEN '&nbsp;'
                    ELSE mepnt
                 END            AS 親機種
                ,CASE
                    WHEN madat IS NULL THEN '&nbsp;'
                    ELSE to_char(madat, 'FM9999/99/99')
                 END            AS 登録日
            FROM
                miitem
            WHERE
                mipn like '{$this->partsKey}%'
            ORDER BY
                parts_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($parts_no, &$result)
    {
        $query = "
            SELECT mipn
                ,midsc
                ,mzist
                ,mepnt
                ,to_char(madat, 'FM9999/99/99')
            FROM
                miitem
            WHERE
                mipn = '{$parts_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('parts_name', $res[0][1]);
            $result->add_once('partsMate',  $res[0][2]);
            $result->add_once('partsParent',$res[0][3]);
            $result->add_once('partsASReg', $res[0][4]);
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
    private function add_execute($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg)
    {
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録は miitemにはない
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into miitem
            (mipn, midsc, mzist, mepnt, madat, last_date, last_user)
            values
            ('$parts_no', '$parts_name', '$partsMate', '$partsParent', $partsASReg, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// 設備･機械のインターフェース マスター 変更
    private function chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg)
    {
        // 保存用のSQL文を設定
        $save_sql = "select * from miitem where mipn='{$preParts_no}'";
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE miitem SET
            mipn='{$parts_no}', midsc='{$parts_name}', mzist='{$partsMate}', mepnt='{$partsParent}', madat={$partsASReg}, last_date='{$last_date}', last_user='{$last_user}'
            WHERE mipn='{$preParts_no}'
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械のインターフェース マスター 削除(完全)
    private function del_execute($parts_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from miitem where mipn='{$parts_no}'";
        $delete_sql = "delete from miitem where mipn='{$parts_no}'";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// AS/400 の登録日を編集した場合のエラーチェックメソッド
    private function ASRegCheck($partsASReg)
    {
        if ($partsASReg == '') {
            $partsASReg = date('Ymd');
        } else {
            $partsASReg = str_replace('/', '', $partsASReg);    // 2005/09/13 → 20050913 へ
            if ($partsASReg > date('Ymd') || $partsASReg < 19700101) {
                $_SESSION['s_sysmsg'] = 'AS登録日が異常値です！ 登録出来ません。';
                return false;
            }
        }
        return $partsASReg;
    }
    
} // Class EquipMacMstMnt_Model End

?>
