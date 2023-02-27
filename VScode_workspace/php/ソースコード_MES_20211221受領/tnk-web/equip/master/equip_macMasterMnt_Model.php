<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ޥ����� �� �Ȳ� �� ���ƥʥ�                               //
//              MVC Model ��                                                //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created equip_mac_mst_mnt.php �� equip_macMasterMnt_Model.php //
// 2002/08/08 register_globals = Off �б�                                   //
// 2003/06/17 servey(�ƻ�ե饰) Y/N ���ѹ��Ǥ��ʤ��Զ����� �ڤ�        //
//              �����ϥե������ץ�����󼰤��ѹ�                          //
// 2003/06/19 $uniq = uniqid('script')���ɲä��� JavaScript File��ɬ���ɤ�  //
// 2004/03/04 ���ǥơ��֥� equip_machine_master2 �ؤ��б�                   //
// 2004/07/12 Netmoni & FWS ���������� �����å����� ���Τ��� Net&FWS�����ɲ�//
//            CSV ������������ �ƻ������� ����̾�ѹ�                        //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/24 �ǥ��쥯�ȥ��ѹ� equip/ �� equip/master/                      //
// 2005/06/28 MVC��Model�����ѹ�  equip_macMasterMnt_Model.php              //
// 2005/07/27 daoInterfaceClass��extends�����Τ�equip_function.php�򳰤���  //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 �ڡ�������ǡ����μ����� $model->get_htmlGETparm()�ǹԤ�      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class
// require_once ('../equip_function.php');     // �����ط� ���Ѵؿ�


/****************************************************************************
*   �����ޥ������� MVC��Model���� base class ���쥯�饹�����               *
****************************************************************************/
class EquipMacMstMnt_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($factory='', $request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_machine_master2 WHERE factory LIKE '{$factory}%'
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'equip_macMaster.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no} limit 1";
            if ($this->getUniResult($query, $check) > 0) {     // ������Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                $response = $this->add_execute($mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ����� �ѹ�
    public function table_change($pmac_no,$mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$pmac_no}";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����ε����ֹ椬��Ͽ����Ƥ��뤫��
                $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no}";
                if ($pmac_no != $mac_no) {
                    if ($this->getUniResult($query, $check) > 0) {    // �ѹ���ε����ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } else {
                        $response = $this->chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    $response = $this->chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$pmac_no} ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($mac_no)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_master2 where mac_no={$mac_no} limit 1";
            if ($this->getUniResult($query, $check) < 1) {     // �����ֹ��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($mac_no);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '����Ǥ��ޤ���Ǥ�����';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��
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
                    $res[$r][5] = 'ͭ��';
                } else {
                    $res[$r][5] = '̵��';
                }
                ///// ���󥿡��ե�����
                if ($res[$r][6] == '0')     $res[$r][6] = '�ʤ�';
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
                else   $res[$r][6] = '����¾';
                ///// ��������
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
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
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
    ////////// �����������ޥ����� �ɲ�
    private function add_execute($mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu)
    {
        if ($denryoku == '') $denryoku = 'NULL';
        if ($keisuu == '') $keisuu = 'NULL'; 
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ
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
    
    ////////// �����������ޥ����� �ѹ�
    private function chg_execute($pmac_no, $mac_no, $mac_name, $maker_name, $maker, $factory, $survey, $csv_flg, $sagyouku, $denryoku, $keisuu)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_machine_master2 where mac_no={$pmac_no}";
        if ($denryoku == '') $denryoku = 'NULL';
        if ($keisuu == '') $keisuu = 'NULL'; 
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_machine_master2 set
            mac_no={$mac_no}, mac_name='$mac_name', maker_name='$maker_name',maker='$maker', factory='$factory',
            survey='$survey', csv_flg=$csv_flg, sagyouku=$sagyouku, denryoku=$denryoku, keisuu=$keisuu,
            last_date='$last_date', last_user='$last_user'
            where mac_no={$pmac_no}
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������ޥ����� ���(����)
    private function del_execute($mac_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_machine_master2 where mac_no={$mac_no}";
        $delete_sql = "delete from equip_machine_master2 where mac_no={$mac_no}";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
