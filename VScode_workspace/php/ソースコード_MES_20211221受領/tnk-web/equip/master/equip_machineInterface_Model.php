<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư�����ε����ȥ��󥿡��ե������Υ�졼����� �Ȳ�����ƥʥ�    //
//              MVC Model ��                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/27 Created   equip_machineInterface_Model.php                    //
//            daoInterfaceClass��extends�����Τ�equip_function.php�򳰤���  //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 �ڡ�������ǡ����μ����� $model->get_htmlGETparm()�ǹԤ�      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class
// require_once ('../equip_function.php');     // �����ط� ���Ѵؿ�


/******************************************************************************
* ������Υ��󥿡��ե����������� MVC��Model���� base class ���쥯�饹�����   *
******************************************************************************/
class EquipMachineInterface_Model extends ComTableMnt
{
    ///// Private properties
    private $factory;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
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
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'equip_machineInterface.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($mac_no, $interface, $csv, $file_name)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // mac_no & interface����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ���󥿡��ե������ֹ�:{$interface} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                $response = $this->add_execute($mac_no, $interface, $csv, $file_name);
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
    public function table_change($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_machine_interface where mac_no={$preMac_no} and interface='{$preInterface}'";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����ε����ֹ�ȥ��󥿡��ե������ֹ椬��Ͽ����Ƥ��뤫��
                $chk_sql1 = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}'";
                if ( ($preMac_no != $mac_no) || ($preInterface != $interface) ) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���ε����ֹ�ȥ��󥿡��ե������ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ���󥿡��ե������ֹ�:{$interface} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } else {
                        $response = $this->chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    // $csv, $file_name �Τߤ��ѹ��Τ��� ¨�ѹ��¹�
                    $response = $this->chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$preMac_no} ���󥿡��ե������ֹ�:{$preInterface}  ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($mac_no, $interface)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select mac_no from equip_machine_interface where mac_no={$mac_no} and interface='{$interface}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // �����ֹ�ȥ��󥿡��ե������ֹ��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ���󥿡��ե������ֹ�:{$interface} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($mac_no, $interface);
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
        $query = "SELECT inter.mac_no
                        ,substr(mac_name, 1, 10)
                        ,inter.interface
                        ,substr(inmas.host, 1, 20)
                        ,CASE
                            WHEN inter.csv=0 THEN '�ʤ�'
                            WHEN inter.csv=1 THEN '����'
                            WHEN inter.csv=2 THEN '����'
                         END        AS ������
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
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
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
    
    ///// ñ�Τε���̾�Τ��֤�(��ǧ������)
    public function getViewMacName($mac_no='')
    {
        if ($mac_no == '') return '&nbsp;';
        $query = "SELECT substr(mac_name, 1, 20) FROM equip_machine_master2 WHERE mac_no={$mac_no}";
        $name = '̤��Ͽ';
        $this->getUniResult($query, $name);
        return $name;
    }
    
    ///// ���󥿡��ե�����(�ۥ���)̾���֤�(��ǧ������)
    public function getViewInterfaceName($interface='')
    {
        if ($interface == '') return '&nbsp;';
        $query = "SELECT substr(host, 1, 20) FROM equip_interface_master WHERE interface={$interface}";
        $name = '̤��Ͽ';
        $this->getUniResult($query, $name);
        return $name;
    }
    
    ///// �ץ�ѥƥ��ι����ʬ���鵡���ֹ�ȵ���̾��������֤�
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
    
    ///// ���󥿡��ե������ֹ�ȥۥ���̾��������֤�
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
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ɲ�
    private function add_execute($mac_no, $interface, $csv, $file_name)
    {
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ
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
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($preMac_no, $preInterface, $mac_no, $interface, $csv, $file_name)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from equip_machine_interface where mac_no={$preMac_no} and interface={$preInterface}";
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_machine_interface set
            mac_no={$mac_no}, interface={$interface}, csv={$csv}, file_name='{$file_name}', last_date='{$last_date}', last_user='{$last_user}'
            where mac_no={$preMac_no} and interface={$preInterface}
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($mac_no, $interface)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_machine_interface where mac_no={$mac_no} and interface={$interface}";
        $delete_sql = "delete from equip_machine_interface where mac_no={$mac_no} and interface={$interface}";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
