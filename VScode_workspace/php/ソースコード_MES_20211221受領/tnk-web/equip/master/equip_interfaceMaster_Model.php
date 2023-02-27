<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��󥿡��ե������ޥ����� �Ȳ�����ƥʥ�                  //
//              MVC Model ��                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/13 Created   equip_interfaceMaster_Model.php                     //
//            boolean ���������� 't', 'f', '1', '0', 'y', 'n', TRUE, FALSE  //
// 2005/07/17 �ǡ����ѹ�����IP���ɥ쥹�����å����å����ѹ�                //
// 2005/07/27 daoInterfaceClass��extends�����Τ�equip_function.php�򳰤���  //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 �ڡ�������ǡ����μ����� $model->get_htmlGETparm()�ǹԤ�      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class
// require_once ('../equip_function.php');     // �����ط� ���Ѵؿ�


/******************************************************************************
* �����Υ��󥿡��ե������ޥ������� MVC��Model���� base class ���쥯�饹����� *
******************************************************************************/
class EquipInterfaceMaster_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_interface_master WHERE interface != 0
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'equip_interfaceMaster.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select interface from equip_interface_master where interface={$interface} limit 1";
            $chk_sql2 = "select interface from equip_interface_master where ip_address='{$ip_address}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // interface����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "���󥿡��ե������ֹ�:{$interface} �ϴ�����Ͽ����Ƥ��ޤ�";
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addres����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "IP���ɥ쥹:{$ip_address} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                $response = $this->add_execute($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
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
    public function table_change($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select interface from equip_interface_master where interface={$preInterface}";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����Υ��󥿡��ե������ֹ椬��Ͽ����Ƥ��뤫��
                $chk_sql1 = "select interface from equip_interface_master where interface={$interface}";
                $chk_sql2 = "select interface from equip_interface_master where ip_address='{$ip_address}' and interface != {$preInterface} limit 1";
                if ($preInterface != $interface) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���Υ��󥿡��ե������ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "���󥿡��ե������ֹ�:{$interface} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addres����Ͽ�ѤߤΥ����å�
                        $_SESSION['s_sysmsg'] = "IP���ɥ쥹:{$ip_address} �ϴ�����Ͽ����Ƥ��ޤ�";
                    } else {
                        $response = $this->chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    if ($this->getUniResult($chk_sql2, $check) > 0) {    // ip_addres����Ͽ�ѤߤΥ����å�
                        $_SESSION['s_sysmsg'] = "IP���ɥ쥹:{$ip_address} �ϴ�����Ͽ����Ƥ��ޤ�";
                    } else {
                        $response = $this->chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "���󥿡��ե������ֹ�:{$preInterface} ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($interface)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select interface from equip_interface_master where interface={$interface}";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // ���󥿡��ե������ֹ��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "���󥿡��ե������ֹ�:{$interface} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($interface);
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
                    $res[$r][5] = 'ͭ��';
                } else {
                    $res[$r][5] = '̵��';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
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
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ɲ�
    private function add_execute($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        if ($ftp_active == 't') $ftp_active = 'TRUE'; else $ftp_active = 'FALSE';
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ
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
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from equip_interface_master where interface={$preInterface}";
        if ($ftp_active == 't') $ftp_active = 'TRUE'; else $ftp_active = 'FALSE';
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_interface_master set
            interface={$interface}, host='{$host}', ip_address='{$ip_address}',ftp_user='{$ftp_user}',
            ftp_pass='{$ftp_pass}', ftp_active={$ftp_active}, last_date='{$last_date}', last_user='{$last_user}'
            where interface={$preInterface}
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($interface)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_interface_master where interface={$interface}";
        $delete_sql = "delete from equip_interface_master where interface={$interface}";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
