<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//              MVC Model ��                                                //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_Model.php                         //
//            boolean ���������� 't', 'f', '1', '0', 'y', 'n', TRUE, FALSE  //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 �ڡ�������ǡ����μ����� $model->get_htmlGETparm()�ǹԤ�      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class
// require_once ('../equip_function.php');     // �����ط� ���Ѵؿ�


/*****************************************************************************************
* ������Ư�����Υ��롼��(����)��ʬ �ޥ������� MVC��Model���� base class ���쥯�饹����� *
*****************************************************************************************/
class EquipGroupMaster_Model extends ComTableMnt
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        $sql_sum = "
            SELECT count(*) FROM equip_group_master
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'equip_groupMaster.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($group_no, $group_name, $active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql1 = "select group_no from equip_group_master where group_no={$group_no} limit 1";
            $chk_sql2 = "select group_no from equip_group_master where group_name='{$group_name}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // group_no����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "�����ʬ(���롼�ץ�����):{$group_no} �ϴ�����Ͽ����Ƥ��ޤ�";
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // group_name����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "����̾(���롼��̾):{$group_name} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                $response = $this->add_execute($group_no, $group_name, $active);
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
    public function table_change($preGroup_no, $group_no, $group_name, $active)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $query = "select group_no from equip_group_master where group_no={$preGroup_no}";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����Υ��롼�ץ����ɤ���Ͽ����Ƥ��뤫��
                $chk_sql1 = "select group_no from equip_group_master where group_no={$group_no}";
                $chk_sql2 = "select group_no from equip_group_master where group_name='{$group_name}' and group_no != {$preGroup_no} limit 1";
                if ($preGroup_no != $group_no) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���Υ��롼�ץ����ɤ�������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "�����ʬ(���롼�ץ�����):{$group_no} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } elseif ($this->getUniResult($chk_sql2, $check) > 0) {    // group_name����Ͽ�ѤߤΥ����å�
                        $_SESSION['s_sysmsg'] = "����̾(���롼��̾):{$group_name} �ϴ�����Ͽ����Ƥ��ޤ�";
                    } else {
                        $response = $this->chg_execute($preGroup_no, $group_no, $group_name, $active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    if ($this->getUniResult($chk_sql2, $check) > 0) {    // group_name����Ͽ�ѤߤΥ����å�
                        $_SESSION['s_sysmsg'] = "����̾(���롼��̾):{$group_name} �ϴ�����Ͽ����Ƥ��ޤ�";
                    } else {
                        $response = $this->chg_execute($preGroup_no, $group_no, $group_name, $active);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "�����ʬ(���롼�ץ�����):{$preGroup_no} ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '���������Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($group_no)
    {
        if (equipAuthUser('FNC_MASTER')) {
            $chk_sql = "select group_no from equip_group_master where group_no={$group_no}";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // group_no��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "�����ʬ(���롼�ץ�����):{$group_no} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($group_no);
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
        $query = "SELECT group_no       AS group_no
                        ,group_name     AS name
                        ,CASE
                            WHEN active IS TRUE THEN 'ͭ��'
                            ELSE '̵��'
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
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
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
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ɲ�
    private function add_execute($group_no, $group_name, $active)
    {
        if ($active == 't') $active = 'TRUE'; else $active = 'FALSE';
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ
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
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($preGroup_no, $group_no, $group_name, $active)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from equip_group_master where group_no={$preGroup_no}";
        if ($active == 't') $active = 'TRUE'; else $active = 'FALSE';
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE equip_group_master SET
            group_no={$group_no}, group_name='{$group_name}', active={$active}, last_date='{$last_date}', last_user='{$last_user}'
            WHERE group_no={$preGroup_no}
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($group_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from equip_group_master where group_no={$group_no}";
        $delete_sql = "delete from equip_group_master where group_no={$group_no}";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
} // Class EquipMacMstMnt_Model End

?>
