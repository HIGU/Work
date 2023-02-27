<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のグループ(工場)区分 マスター 照会＆メンテナンス               //
//              MVC Model 部                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_Model.php                         //
//            boolean 型の代入は 't', 'f', '1', '0', 'y', 'n', TRUE, FALSE  //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ページ制御データの取得は $model->get_htmlGETparm()で行う      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class
// require_once ('../equip_function.php');     // 設備関係 共用関数


/*****************************************************************************************
* 設備稼働管理のグループ(工場)区分 マスター用 MVCのModel部の base class 基底クラスの定義 *
*****************************************************************************************/
class EquipGroupMaster_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_group_master
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'equip_groupMaster.log');
    }
    
    ////////// マスター追加
    public function table_add($group_no, $group_name, $active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select group_no from equip_group_master where group_no={$group_no} limit 1";
            $chk_sql2 = "select group_no from equip_group_master where group_name='{$group_name}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // group_noの登録済みのチェック
                $_SESSION['s_sysmsg'] = "工場区分(グループコード):{$group_no} は既に登録されています";
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // group_nameの登録済みのチェック
                $_SESSION['s_sysmsg'] = "工場名(グループ名):{$group_name} は既に登録されています";
            } else {
                $response = $this->add_execute($group_no, $group_name, $active);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '登録できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスター 変更
    public function table_change($preGroup_no, $group_no, $group_name, $active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select group_no from equip_group_master where group_no={$preGroup_no}";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前のグループコードが登録されているか？
                $chk_sql1 = "select group_no from equip_group_master where group_no={$group_no}";
                $chk_sql2 = "select group_no from equip_group_master where group_name='{$group_name}' and group_no != {$preGroup_no} limit 1";
                if ($preGroup_no != $group_no) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // 変更後のグループコードが既に登録されているか？
                        $_SESSION['s_sysmsg'] = "工場区分(グループコード):{$group_no} は既に登録されています！";
                    } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // group_nameの登録済みのチェック
                        $_SESSION['s_sysmsg'] = "工場名(グループ名):{$group_name} は既に登録されています";
                    } else {
                        $response = $this->chg_execute($preGroup_no, $group_no, $group_name, $active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                } else {
                    if ($this->getUniResult($chk_sql2, $check) > 0) {    // group_nameの登録済みのチェック
                        $_SESSION['s_sysmsg'] = "工場名(グループ名):{$group_name} は既に登録されています";
                    } else {
                        $response = $this->chg_execute($preGroup_no, $group_no, $group_name, $active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "工場区分(グループコード):{$preGroup_no} は他の人に変更されました！";
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($group_no)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select group_no from equip_group_master where group_no={$group_no}";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // group_noの存在チェック
                $_SESSION['s_sysmsg'] = "工場区分(グループコード):{$group_no} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($group_no);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '削除できませんでした。';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// MVC の Model 部の結果 表示用のデータ取得
    ///// List部
    public function getViewDataList(&$result)
    {
        $query = "SELECT group_no       AS group_no
                        ,group_name     AS name
                        ,CASE
                            WHEN active IS TRUE THEN '有効'
                            ELSE '無効'
                         END            AS active
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                    FROM
                        equip_group_master
                    ORDER BY
                        group_no
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($group_no, &$result)
    {
        $query = "SELECT group_no
                        ,group_name
                        ,active
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                    FROM
                        equip_group_master
                    WHERE
                        group_no = {$group_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('group_name', $res[0][1]);
            $result->add_once('active',     $res[0][2]);
            $result->add_once('regdate',    $res[0][3]);
            $result->add_once('last_date',  $res[0][4]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 設備･機械のインターフェース マスター 追加
    private function add_execute($group_no, $group_name, $active)
    {
        if ($active == 't') $active = 'TRUE'; else $active = 'FALSE';
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into equip_group_master
            (group_no, group_name, active, last_date, last_user)
            values
            ($group_no, '$group_name', $active, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// 設備･機械のインターフェース マスター 変更
    private function chg_execute($preGroup_no, $group_no, $group_name, $active)
    {
        // 保存用のSQL文を設定
        $save_sql = "select * from equip_group_master where group_no={$preGroup_no}";
        if ($active == 't') $active = 'TRUE'; else $active = 'FALSE';
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE equip_group_master SET
            group_no={$group_no}, group_name='{$group_name}', active={$active}, last_date='{$last_date}', last_user='{$last_user}'
            WHERE group_no={$preGroup_no}
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械のインターフェース マスター 削除(完全)
    private function del_execute($group_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from equip_group_master where group_no={$group_no}";
        $delete_sql = "delete from equip_group_master where group_no={$group_no}";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
