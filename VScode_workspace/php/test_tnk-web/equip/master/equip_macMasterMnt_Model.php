<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター の 照会 ＆ メンテナンス                               //
//              MVC Model 部                                                //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created equip_mac_mst_mnt.php → equip_macMasterMnt_Model.php //
// 2002/08/08 register_globals = Off 対応                                   //
// 2003/06/17 servey(監視フラグ) Y/N が変更できない不具合を修正 及び        //
//              各入力フォームをプルダウン式に変更                          //
// 2003/06/19 $uniq = uniqid('script')を追加して JavaScript Fileを必ず読む  //
// 2004/03/04 新版テーブル equip_machine_master2 への対応                   //
// 2004/07/12 Netmoni & FWS 方式を統一 スイッチ方式 そのため Net&FWS方式追加//
//            CSV 出力設定等を 監視方式へ 項目名変更                        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/24 ディレクトリ変更 equip/ → equip/master/                      //
// 2005/06/28 MVCのModel部へ変更  equip_macMasterMnt_Model.php              //
// 2005/07/27 daoInterfaceClassをextendsしたのでequip_function.phpを外した  //
// 2005/08/18 ページ制御データをComTableMntClassへ移行してカプセル化        //
// 2005/08/19 ページ制御データの取得は $model->get_htmlGETparm()で行う      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード

require_once ('../../ComTableMntClass.php');// TNK 全共通 テーブルメンテ&ページ制御Class
// require_once ('../equip_function.php');     // 設備関係 共用関数


/****************************************************************************
*   機械マスター用 MVCのModel部の base class 基底クラスの定義               *
****************************************************************************/
class EquipMacMstMnt_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer の定義 (php5へ移行時は __construct() へ変更予定) (デストラクタ__destruct())
    public function __construct($factory='', $request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_machine_master2 WHERE factory LIKE '{$factory}%'
        ";
        ///// Constructer を定義すると 基底クラスの Constructerが実行されない
        ///// 基底ClassのConstructerはプログラマーの責任で呼出す
        parent::__construct($sql_sum, $request, 'equip_macMaster.log');
    }
    
    ////////// マスター追加
    public function table_add($mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no} limit 1";
            if ($this->getUniResult($query, $check) > 0) {     // 既に登録済みのチェック
                $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} は既に登録されています";
            } else {
                $response = $this->add_execute($mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
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
    public function table_change($pmac_no,$mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$pmac_no}";
            if ($this->getUniResult($query, $check) > 0) {  // 変更前の機械番号が登録されているか？
                $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no}";
                if ($pmac_no != $mac_no) {
                    if ($this->getUniResult($query, $check) > 0) {    // 変更後の機械番号が既に登録されているか？
                        $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} は既に登録されています！";
                    } else {
                        $response = $this->chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '変更できませんでした。';
                        }
                    }
                } else {
                    $response = $this->chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '変更できませんでした。';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "機械番号:{$pmac_no} は他の人に変更されました！";
            }
        } else {
            $_SESSION['s_sysmsg'] = '設備管理のマスター編集権限がありません！';
        }
        return false;
    }
    
    ////////// マスターの完全削除
    public function table_delete($mac_no)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no} limit 1";
            if ($this->getUniResult($query, $check) < 1) {     // 機械番号の存在チェック
                $_SESSION['s_sysmsg'] = "機械番号:{$mac_no} は他の人に変更されました！";
            } else {
                $response = $this->del_execute($mac_no);
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
    public function getViewDataList($factoryList, &$result)
    {
        $query = "SELECT mac_no
                        ,mac_name
                        ,maker_name
                        ,maker
                        ,factory
                        ,survey
                        ,csv_flg
                        ,sagyouku
                        ,denryoku
                        ,keisuu 
                    FROM
                        equip_machine_master2
                    WHERE
                        factory LIKE '{$factoryList}%'
                    ORDER BY
                        mac_no
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) >= 1 ) {
        // if ( ($rows=$this->getResult2($query, $res)) >= 1 ) {
            for($r=0; $r<$rows; $r++) {
                if ($res[$r][5] == 'Y') {
                    $res[$r][5] = '有効';
                } else {
                    $res[$r][5] = '無効';
                }
                ///// インターフェース
                if ($res[$r][6] == '0')     $res[$r][6] = 'なし';
                elseif ($res[$r][6] == '1') $res[$r][6] = 'Netmoni';
                elseif ($res[$r][6] == '2') $res[$r][6] = 'FWS1';
                elseif ($res[$r][6] == '3') $res[$r][6] = 'FWS2';
                elseif ($res[$r][6] == '4') $res[$r][6] = 'FWS3';
                elseif ($res[$r][6] == '5') $res[$r][6] = 'FWS4';
                elseif ($res[$r][6] == '6') $res[$r][6] = 'FWS5';
                elseif ($res[$r][6] == '7') $res[$r][6] = 'FWS6';
                elseif ($res[$r][6] == '8') $res[$r][6] = 'FWS7';
                elseif ($res[$r][6] == '9') $res[$r][6] = 'FWS8';
                elseif ($res[$r][6] == '10') $res[$r][6] = 'FWS9';
                elseif ($res[$r][6] == '11') $res[$r][6] = 'FWS10';
                elseif ($res[$r][6] == '12') $res[$r][6] = 'FWS11';
                elseif ($res[$r][6] == '101') $res[$r][6] = 'Net&FWS';
                else   $res[$r][6] = 'その他';
                ///// 使用電力
                if ($res[$r][8] == '') {
                    $res[$r][8] = '-';
                } else {
                    $res[$r][8] = number_format($res[$r][8], 2);
                }
                if ($res[$r][9] == '') {
                    $res[$r][9] = '-';
                } else {
                    $res[$r][9] = number_format($res[$r][9], 2);
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '登録がありません！';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1レコード分
    public function getViewDataEdit($mac_no, &$result)
    {
        $query = "SELECT mac_no
                        ,mac_name
                        ,maker_name
                        ,maker
                        ,factory
                        ,survey
                        ,csv_flg
                        ,sagyouku
                        ,denryoku
                        ,keisuu 
                    FROM
                        equip_machine_master2
                    WHERE
                        mac_no = {$mac_no}
                    ORDER BY
                        mac_no
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('mac_name', $res[0][1]);
            $result->add_once('maker_name', $res[0][2]);
            $result->add_once('maker', $res[0][3]);
            $result->add_once('factory', $res[0][4]);
            $result->add_once('survey', $res[0][5]);
            $result->add_once('csv_flg', $res[0][6]);
            $result->add_once('sagyouku', $res[0][7]);
            $result->add_once('denryoku', $res[0][8]);
            $result->add_once('keisuu', $res[0][9]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// 設備･機械マスター 追加
    private function add_execute($mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu)
    {
        if ($denryoku == '') $denryoku = 'NULL';
        if ($keisuu == '') $keisuu = 'NULL'; 
        // ここに last_date last_user の登録処理を入れる
        // regdate=自動登録
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_sql = "
            insert into equip_machine_master2
            (mac_no, mac_name, maker_name, maker, factory, survey, csv_flg, sagyouku, denryoku, keisuu, last_date, last_user)
            values
            ($mac_no, '$mac_name', '$maker_name', '$maker', '$factory', '$survey', $csv_flg, '$sagyouku', $denryoku, $keisuu, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// 設備･機械マスター 変更
    private function chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from equip_machine_master2 where mac_no={$pmac_no}";
        if ($denryoku == '') $denryoku = 'NULL';
        if ($keisuu == '') $keisuu = 'NULL'; 
        // ここに last_date last_user の登録処理を入れる
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_machine_master2 set
            mac_no={$mac_no}, mac_name='$mac_name', maker_name='$maker_name',maker='$maker', factory='$factory',
            survey='$survey', csv_flg=$csv_flg, sagyouku=$sagyouku, denryoku=$denryoku, keisuu=$keisuu,
            last_date='$last_date', last_user='$last_user'
            where mac_no={$pmac_no}
        "; 
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// 設備･機械マスター 削除(完全)
    private function del_execute($mac_no)
    {
        // 保存用のSQL文を設定
        $save_sql   = "select * from equip_machine_master2 where mac_no={$mac_no}";
        $delete_sql = "delete from equip_machine_master2 where mac_no={$mac_no}";
        // $save_sqlはオプションなので指定しなくても良い
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
