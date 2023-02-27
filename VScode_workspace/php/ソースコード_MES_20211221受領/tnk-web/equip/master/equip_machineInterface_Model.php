<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理の機械とインターフェースのリレーション 照会＆メンテナンス    //
//              MVC Model 部                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/27 Created   equip_machineInterface_Model.php                    //
//            daoInterfaceClassをextendsしたのでequip_function.phpを外した  //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ページ制御データの取得は $model->get_htmlGETparm()で行う      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class
// require_once ('../equip_function.php');     // 設備関係 共用関数


/******************************************************************************
* 機械毎のインターフェース設定用 MVCのModel部の base class 基底クラスの定義   *
******************************************************************************/
class EquipMachineInterface_Model extends ComTableMnt
{
    ///// Private properties
    private $factory;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($factory='', $request)
    {
        $this->factory = $factory;
        $sql_sum = "
            SELECT count(*)
            FROM equip_machine_interface
            LEFT OUTER JOIN equip_machine_master2 AS mac
            USING(mac_no)
            WHERE mac.factory LIKE '{$factory}%'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'equip_machineInterface.log');
    }
    
    ////////// マスター追加
    public function table_add($mac_no, $interface, $csv, $file_name)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // mac_no & interfaceの登録済みのチェック
                $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} インターフェース番号:{$interface} は既に登録されています";
            } else {
                $response = $this->add_execute($mac_no, $interface, $csv, $file_name);
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
    public function table_change($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_interface where mac_no={$preMac_no} and interface='{$preInterface}'";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前の機械番号とインターフェース番号が登録されているか？
                $chk_sql1 = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}'";
                if ( ($preMac_no != $mac_no) || ($preInterface != $interface) ) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // 変更後の機械番号とインターフェース番号が既に登録されているか？
                        $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} インターフェース番号:{$interface} は既に登録されています！";
                    } else {
                        $response = $this->chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                } else {
                    // $csv, $file_name のみの変更のため 即変更実行
                    $response = $this->chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '変更できませんでした。';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "機械番号:{$preMac_no} インターフェース番号:{$preInterface}  は他の人に変更されました！";
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($mac_no, $interface)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // 機械番号とインターフェース番号の存在チェック
                $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} インターフェース番号:{$interface} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($mac_no, $interface);
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
        $query = "SELECT inter.mac_no
                        ,substr(mac_name, 1, 10)
                        ,inter.interface
                        ,substr(inmas.host, 1, 20)
                        ,CASE
                            WHEN inter.csv=0 THEN 'なし'
                            WHEN inter.csv=1 THEN '出力'
                            WHEN inter.csv=2 THEN '入力'
                         END        AS 入出力
                        ,CASE
                            WHEN inter.file_name='' THEN '&nbsp;'
                            ELSE inter.file_name
                         END        AS file_name
                        ,to_char(inter.regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                        ,to_char(inter.last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                    FROM
                        equip_machine_interface     AS inter
                    LEFT OUTER JOIN
                        equip_machine_master2       AS mac
                    USING(mac_no)
                    LEFT OUTER JOIN
                        equip_interface_master      AS inmas
                    USING(interface)
                    WHERE
                        mac.factory LIKE '{$this->factory}%'
                    ORDER BY
                        inter.mac_no ASC, interface ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($mac_no, $interface, &$result)
    {
        $query = "SELECT mac_no
                        ,interface
                        ,csv
                        ,file_name
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                    FROM
                        equip_machine_interface
                    WHERE
                        mac_no = {$mac_no}
                        and
                        interface = '{$interface}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('csv',        $res[0][2]);
            $result->add_once('file_name',  $res[0][3]);
            $result->add_once('regdate',    $res[0][4]);
            $result->add_once('last_date',  $res[0][5]);
        }
        return $rows;
    }
    
    ///// 単体の機械名称を返す(確認画面用)
    public function getViewMacName($mac_no='')
    {
        if ($mac_no == '') return '&nbsp;';
        $query = "SELECT substr(mac_name, 1, 20) FROM equip_machine_master2 WHERE mac_no={$mac_no}";
        $name = '未登録';
        $this->getUniResult($query, $name);
        return $name;
    }
    
    ///// インターフェース(ホスト)名を返す(確認画面用)
    public function getViewInterfaceName($interface='')
    {
        if ($interface == '') return '&nbsp;';
        $query = "SELECT substr(host, 1, 20) FROM equip_interface_master WHERE interface={$interface}";
        $name = '未登録';
        $this->getUniResult($query, $name);
        return $name;
    }
    
    ///// プロパティの工場区分から機械番号と機械名の配列を返す
    public function getViewMac_noName(&$result)
    {
        if ($this->factory == '') $where = '';
        else $where = " and factory = '{$this->factory}'";
        $query = "SELECT mac_no
                    , to_char(mac_no, '0000 ') || mac_name AS mac_no_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    {$where}
                order by mac_no ASC
        ";
        $result = array();
        return $this->getResult2($query, $result);
    }
    
    ///// インターフェース番号とホスト名の配列を返す
    public function getViewInterName(&$result)
    {
        $query = "SELECT interface
                        , host
                from
                    equip_interface_master
                where
                    ftp_active IS TRUE
                order by interface ASC
        ";
        $result = array();
        return $this->getResult2($query, $result);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 設備･機械のインターフェース マスター 追加
    private function add_execute($mac_no, $interface, $csv, $file_name)
    {
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into equip_machine_interface
            (mac_no, interface, csv, file_name, last_date, last_user)
            values
            ($mac_no, $interface, $csv, '$file_name', '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// 設備･機械のインターフェース マスター 変更
    private function chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name)
    {
        // 保存用のSQL文を設定
        $save_sql = "select * from equip_machine_interface where mac_no={$preMac_no} and interface={$preInterface}";
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_machine_interface set
            mac_no={$mac_no}, interface={$interface}, csv={$csv}, file_name='{$file_name}', last_date='{$last_date}', last_user='{$last_user}'
            where mac_no={$preMac_no} and interface={$preInterface}
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械のインターフェース マスター 削除(完全)
    private function del_execute($mac_no, $interface)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from equip_machine_interface where mac_no={$mac_no} and interface={$interface}";
        $delete_sql = "delete from equip_machine_interface where mac_no={$mac_no} and interface={$interface}";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
