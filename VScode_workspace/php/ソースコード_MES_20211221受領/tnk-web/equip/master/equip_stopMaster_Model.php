<?php
//////////////////////////////////////////////////////////////////////////////
// ��������������ߤ����(���ȥå�) �ޥ����� �Ȳ�����ƥʥ�             //
//              MVC Model ��                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_stopMaster_Model.php                          //
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
*     ��������ߤ�����ޥ������� MVC��Model���� base class ���쥯�饹�����   *
******************************************************************************/
class EquipStopMaster_Model extends ComTableMnt
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
            FROM equip_stop_master
            LEFT OUTER JOIN equip_machine_master2 AS mac
            USING(mac_no)
            WHERE mac.factory LIKE '{$factory}%'
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'equip_stopMaster.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($mac_no, $parts_no, $stop)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select mac_no from equip_stop_master where mac_no={$mac_no} and parts_no='{$parts_no}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // mac_no & parts_no����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ����(����)�ֹ�:{$parts_no} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                $response = $this->add_execute($mac_no, $parts_no, $stop);
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
    public function table_change($preMac_no, $preParts_no, $mac_no, $parts_no, $stop)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select mac_no from equip_stop_master where mac_no={$preMac_no} and parts_no='{$preParts_no}'";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����ε����ֹ�������ֹ椬��Ͽ����Ƥ��뤫��
                $chk_sql1 = "select mac_no from equip_stop_master where mac_no={$mac_no} and parts_no='{$parts_no}'";
                if ( ($preMac_no != $mac_no) || ($preParts_no != $parts_no) ) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���ε����ֹ�������ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ����(����)�ֹ�:{$parts_no} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } else {
                        $response = $this->chg_execute($preMac_no, $preParts_no, $mac_no, $parts_no, $stop);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    // $stop �Τߤ��ѹ��Τ��� ¨�ѹ��¹�
                    $response = $this->chg_execute($preMac_no, $preParts_no, $mac_no, $parts_no, $stop);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$preMac_no} ����(����)�ֹ�:{$preParts_no}  ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($mac_no, $parts_no)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select mac_no from equip_stop_master where mac_no={$mac_no} and parts_no='{$parts_no}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // �����ֹ������(����)�ֹ��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "�����ֹ�:{$mac_no} ����(����)�ֹ�:{$parts_no} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($mac_no, $parts_no);
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
        $query = "SELECT stop.mac_no
                        ,substr(mac_name, 1, 10)
                        ,stop.parts_no
                        ,substr(midsc, 1, 20)
                        ,stop.stop
                        ,to_char(stop.regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                        ,to_char(stop.last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI')
                    FROM
                        equip_stop_master       AS stop
                    LEFT OUTER JOIN
                        equip_machine_master2   AS mac  USING(mac_no)
                    LEFT OUTER JOIN
                        miitem ON(parts_no=mipn)
                    WHERE
                        mac.factory LIKE '{$this->factory}%'
                    ORDER BY
                        mac_no ASC, parts_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) >= 1 ) {
            for($r=0; $r<$rows; $r++) {
                if ($res[$r][2] == '000000000') {
                    $res[$r][3] = '������';
                } elseif($res[$r][2] == '999999999') {
                    $res[$r][3] = '�ƥ�����';
                } else {
                    if ($res[$r][3] == '') $res[$r][3] = '����̤��Ͽ';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
    public function getViewDataEdit($mac_no, $parts_no, &$result)
    {
        $query = "SELECT mac_no
                        ,parts_no
                        ,stop
                        ,to_char(regdate AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                        ,to_char(last_date AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS')
                    FROM
                        equip_stop_master
                    WHERE
                        mac_no = {$mac_no}
                        and
                        parts_no = '{$parts_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('stop',      $res[0][2]);
            $result->add_once('regdate',    $res[0][3]);
            $result->add_once('last_date',  $res[0][4]);
        }
        return $rows;
    }
    
    ///// ñ�Τε���̾�Τ��֤�(��ǧ��������)
    public function getViewMacName($mac_no='')
    {
        if ($mac_no == '') return '&nbsp;';
        $query = "SELECT substr(mac_name, 1, 20) FROM equip_machine_master2 WHERE mac_no={$mac_no}";
        $name = '̤��Ͽ';
        $this->getUniResult($query, $name);
        return $name;
    }
    
    ///// ñ�Τ�����̾���֤�(��ǧ��������)
    public function getViewPartsName($parts_no='')
    {
        if ($parts_no == '') return '&nbsp;';
        if ($parts_no == '000000000') return '������';
        if ($parts_no == '999999999') return '�ƥ�����';
        $query = "SELECT substr(midsc, 1, 30) FROM miitem WHERE mipn='{$parts_no}'";
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
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ɲ�
    private function add_execute($mac_no, $parts_no, $stop)
    {
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into equip_stop_master
            (mac_no, parts_no, stop, last_date, last_user)
            values
            ($mac_no, '$parts_no', $stop, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($preMac_no, $preParts_no, $mac_no, $parts_no, $stop)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from equip_stop_master where mac_no={$preMac_no} and parts_no='{$preParts_no}'";
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            update equip_stop_master set
            mac_no={$mac_no}, parts_no='{$parts_no}', stop={$stop}, last_date='{$last_date}', last_user='{$last_user}'
            where mac_no={$preMac_no} and parts_no='{$preParts_no}'
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($mac_no, $parts_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_stop_master where mac_no={$mac_no} and parts_no='{$parts_no}'";
        $delete_sql = "delete from equip_stop_master where mac_no={$mac_no} and parts_no='{$parts_no}'";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
