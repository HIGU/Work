<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械のインターフェースマスター 照会＆メンテナンス                  //
//              MVC Model 部                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/13 Created   equip_interfaceMaster_Model.php                     //
//            boolean 型の代入は 't', 'f', '1', '0', 'y', 'n', TRUE, FALSE  //
// 2005/07/17 データ変更時のIPアドレスチェックロジックを変更                //
// 2005/07/27 daoInterfaceClassをextendsしたのでequip_function.phpを外した  //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ページ制御データの取得は $model->get_htmlGETparm()で行う      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class
// require_once ('../equip_function.php');     // 設備関係 共用関数


/******************************************************************************
* 機械のインターフェースマスター用 MVCのModel部の base class 基底クラスの定義 *
******************************************************************************/
class EquipInterfaceMaster_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_interface_master WHERE interface != 0
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'equip_interfaceMaster.log');
    }
    
    ////////// マスター追加
    public function table_add($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select interface from equip_interface_master where interface={$interface} limit 1";
            $chk_sql2 = "select interface from equip_interface_master where ip_address='{$ip_address}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // interfaceの登録済みのチェック
                $_SESSION['s_sysmsg'] = "インターフェース番号:{$interface} は既に登録されています";
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addresの登録済みのチェック
                $_SESSION['s_sysmsg'] = "IPアドレス:{$ip_address} は既に登録されています";
            } else {
                $response = $this->add_execute($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
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
    public function table_change($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select interface from equip_interface_master where interface={$preInterface}";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前のインターフェース番号が登録されているか？
                $chk_sql1 = "select interface from equip_interface_master where interface={$interface}";
                $chk_sql2 = "select interface from equip_interface_master where ip_address='{$ip_address}' and interface != {$preInterface} limit 1";
                if ($preInterface != $interface) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // 変更後のインターフェース番号が既に登録されているか？
                        $_SESSION['s_sysmsg'] = "インターフェース番号:{$interface} は既に登録されています！";
                    } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addresの登録済みのチェック
                        $_SESSION['s_sysmsg'] = "IPアドレス:{$ip_address} は既に登録されています";
                    } else {
                        $response = $this->chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                } else {
                    if ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addresの登録済みのチェック
                        $_SESSION['s_sysmsg'] = "IPアドレス:{$ip_address} は既に登録されています";
                    } else {
                        $response = $this->chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "インターフェース番号:{$preInterface} は他の人に変更されました！";
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($interface)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select interface from equip_interface_master where interface={$interface}";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // インターフェース番号の存在チェック
                $_SESSION['s_sysmsg'] = "インターフェース番号:{$interface} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($interface);
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
        $query = "SELECT interface
                        ,host
                        ,ip_address
                        ,ftp_user
                        ,ftp_pass
                        ,ftp_active
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                    FROM
                        equip_interface_master
                    WHERE
                        interface != 0
                    ORDER BY
                        interface
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) >= 1 ) {
            for($r=0; $r<$rows; $r++) {
                if ($res[$r][5] == 't') {
                    $res[$r][5] = '有効';
                } else {
                    $res[$r][5] = '無効';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($interface, &$result)
    {
        $query = "SELECT interface
                        ,host
                        ,ip_address
                        ,ftp_user
                        ,ftp_pass
                        ,ftp_active
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                    FROM
                        equip_interface_master
                    WHERE
                        interface = {$interface}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('host',       $res[0][1]);
            $result->add_once('ip_address', $res[0][2]);
            $result->add_once('ftp_user',   $res[0][3]);
            $result->add_once('ftp_pass',   $res[0][4]);
            $result->add_once('ftp_active', $res[0][5]);
            $result->add_once('regdate',    $res[0][6]);
            $result->add_once('last_date',  $res[0][7]);
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
    private function add_execute($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if ($ftp_active == 't') $ftp_active = 'TRUE'; else $ftp_active = 'FALSE';
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into equip_interface_master
            (interface, host, ip_address, ftp_user, ftp_pass, ftp_active, last_date, last_user)
            values
            ($interface, '$host', '$ip_address', '$ftp_user', '$ftp_pass', $ftp_active, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// 設備･機械のインターフェース マスター 変更
    private function chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        // 保存用のSQL文を設定
        $save_sql = "select * from equip_interface_master where interface={$preInterface}";
        if ($ftp_active == 't') $ftp_active = 'TRUE'; else $ftp_active = 'FALSE';
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_interface_master set
            interface={$interface}, host='{$host}', ip_address='{$ip_address}',ftp_user='{$ftp_user}',
            ftp_pass='{$ftp_pass}', ftp_active={$ftp_active}, last_date='{$last_date}', last_user='{$last_user}'
            where interface={$preInterface}
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械のインターフェース マスター 削除(完全)
    private function del_execute($interface)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from equip_interface_master where interface={$interface}";
        $delete_sql = "delete from equip_interface_master where interface={$interface}";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
