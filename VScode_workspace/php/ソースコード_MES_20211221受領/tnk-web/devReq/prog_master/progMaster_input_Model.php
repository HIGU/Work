<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ����ޥ������ξȲ񡦥��� MVC Model ��                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_Model.php                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*       ���������ƥ�����ʡ����ʤΥ����ƥ�ޥ����� MVC��Model���� ��ĥ���饹�����       *
*****************************************************************************************/
class ProgMaster_Model extends ComTableMnt
{
    ///// Private properties
    private $pidKey = '';                         // �����ե������
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $pidKey='')
    {
        if ($pidKey == '') {
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            $this->pidKey = $pidKey;    // Properties�ؤ���Ͽ
        }
        $sql_sum = "
            SELECT count(*) FROM program_master where p_id like '{$pidKey}%'
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'progMaster_input_Master.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql1 = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // pid����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "�ץ����̾��{$pid} �ǥ��쥯�ȥꡧ{$pdir} �ϴ�����Ͽ����Ƥ��ޤ���";
                return false;
            } //else {
                //$response = $this->add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
                //if ($response) {
                //    return true;
                //} else {
                //    $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
                //}
            //}
            if ($db1 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db1}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db1} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db2 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db2}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db2} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db3 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db3}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db3} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db4 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db4}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db4} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db5 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db5}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db5} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db6 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db6}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db6} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db7 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db7}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db7} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db8 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db8}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db8} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db9 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db9}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db9} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db10 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db10}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db10} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db11 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db11}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db11} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            if ($db12 != '') {
                $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db12}'";
                if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                    $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db12} ��¸�ߤ��ޤ���";
                    return false;
                }
            }
            $response = $this->add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
            }
        } else {
            $_SESSION['s_sysmsg'] = '�ץ����Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ����� �ѹ�
    public function table_change($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $query = "select p_id from program_master where p_id='{$prePid}' AND dir='{$preDir}'";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����������ֹ椬��Ͽ����Ƥ��뤫��
                $chk_sql1 = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}'";
                if (($prePid != $pid) || ($preDir != $pdir)) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���������ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "�ץ����̾��{$pid} �ǥ��쥯�ȥꡧ{$pdir} �ϴ�����Ͽ����Ƥ��ޤ���";
                        return false;
                    }
                }
                // �ǡ����١�����¸�ߥ����å�
                if ($db1 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db1}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db1} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db2 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db2}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db2} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db3 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db3}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db3} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db4 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db4}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db4} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db5 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db5}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db5} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db6 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db6}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db6} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db7 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db7}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db7} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db8 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db8}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db8} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db9 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db9}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db9} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db10 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db10}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db10} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db11 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db11}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db11} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                if ($db12 != '') {
                    $chk_sql2 = "SELECT * FROM information_schema.tables WHERE table_name ='{$db12}'";
                    if ($this->getUniResult($chk_sql2, $check) < 1) {    // �ǡ����١�����¸�ߥ����å�
                        $_SESSION['s_sysmsg'] = "�ǡ����١�����{$db12} ��¸�ߤ��ޤ���";
                        return false;
                    }
                }
                $response = $this->chg_execute($prePid, $pid, $pname, $pdir, $preDir,$pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                }
            } else {
                $_SESSION['s_sysmsg'] = "�ץ����̾��{$prePid} �ǥ��쥯�ȥꡧ{$preDir} ��¾�οͤ��ѹ�����ޤ�����{$pid}";
            }
        } else {
            $_SESSION['s_sysmsg'] = '�ץ����Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($pid, $pdir)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql = "select p_id from program_master where p_id='{$pid}' AND dir='{$pdir}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // pid��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "�ץ����̾��{$pid} �ǥ��쥯�ȥꡧ{$pdir} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($pid, $pdir);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '����Ǥ��ޤ���Ǥ�����';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '�ץ����Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��
    public function getViewDataList(&$result)
    {
        ///// ��� $pidKey �ե�����ɤǤθ���
        $query = "
            SELECT p_id         AS �ץ����ID
                ,p_name         AS �ץ����̾
                ,dir            AS �ǥ��쥯�ȥ�
                ,comment        AS ������
                ,db1            AS DB����1
                ,db2            AS DB����2
                ,db3            AS DB����3
                ,db4            AS DB����4
                ,db5            AS DB����5
                ,db6            AS DB����6
                ,db7            AS DB����7
                ,db8            AS DB����8
                ,db9            AS DB����9
                ,db10           AS DB����10
                ,db11           AS DB����11
                ,db12           AS DB����12
                ,last_date      AS ��Ͽ����
            FROM
                program_master
            WHERE
                p_id like '{$this->pidKey}%'
            ORDER BY
                dir ASC, p_id ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
    public function getViewDataEdit($pid, $pdir, &$result)
    {
        $query = "
            SELECT p_id
                ,p_name
                ,dir
                ,comment
                ,db1
                ,db2
                ,db3
                ,db4
                ,db5
                ,db6
                ,db7
                ,db8
                ,db9
                ,db10
                ,db11
                ,db12
                ,last_date
            FROM
                program_master
            WHERE
                p_id = '{$pid}' AND dir = '{$pdir}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('pname', $res[0][1]);
            $result->add_once('pcomment',$res[0][3]);
            $result->add_once('db1', $res[0][4]);
            $result->add_once('db2', $res[0][5]);
            $result->add_once('db3', $res[0][6]);
            $result->add_once('db4', $res[0][7]);
            $result->add_once('db5', $res[0][8]);
            $result->add_once('db6', $res[0][9]);
            $result->add_once('db7', $res[0][10]);
            $result->add_once('db8', $res[0][11]);
            $result->add_once('db9', $res[0][12]);
            $result->add_once('db10', $res[0][13]);
            $result->add_once('db11', $res[0][14]);
            $result->add_once('db12', $res[0][15]);
            $result->add_once('last_date', $res[0][16]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �����ط��Υޥ������Խ����¥᥽�å�(���������ѥ᥽�åɰܹԤ���)
    protected function IndustAuthUser($class)
    {
        // $class �Ͼ���Ū�˻���ͽ�� (MASTER/PLAN/ORDER/...)
        $LoginUser = $_SESSION['User_ID'];
        $query = "select sid from user_detailes where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            if ($sid == 21) {   // ���������ݤʤ�OK
                return true;
            } elseif ($_SESSION['Auth'] >= 3) { // �ƥ�����
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
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ɲ�
    private function add_execute($pid, $pname, $pdir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ�� miitem�ˤϤʤ�
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into program_master
            (p_id, p_name, dir, comment, db1, db2, db3, db4, db5, db6, db7, db8, db9, db10, db11, db12,last_date, last_user)
            values
            ('$pid', '$pname', '$pdir', '$pcomment', '$db1', '$db2', '$db3', '$db4', '$db5', '$db6', '$db7', '$db8', '$db9', '$db10', '$db11', '$db12','$last_date', '$last_user')
        ";
        $this->log_openCheck('progMaster_input_Master.log');
        $this->set_page_rec(20);              // �����(���֤�����)
        return $this->execute_Insert($insert_qry, 'progMaster_input_Master.log');
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($prePid, $pid, $pname, $pdir, $preDir, $pcomment, $db1, $db2, $db3, $db4, $db5, $db6, $db7, $db8, $db9, $db10, $db11, $db12)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from program_master where p_id='{$prePid}' AND dir='{$preDir}'";
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE program_master SET
            p_id='{$pid}', p_name='{$pname}', dir='{$pdir}', comment='{$pcomment}', db1='{$db1}', db2='{$db2}', db3='{$db3}', db4='{$db4}', db5='{$db5}', db6='{$db6}', db7='{$db7}', db8='{$db8}', db9='{$db9}', db10='{$db10}', db11='{$db11}', db12='{$db12}',last_date='{$last_date}', last_user='{$last_user}'
            WHERE p_id='{$prePid}' AND dir='{$preDir}'
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($pid, $pdir)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from program_master where p_id='{$pid}' AND dir='{$pdir}'";
        $delete_sql = "delete from program_master where p_id='{$pid}' AND dir='{$pdir}'";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
} // Class EquipMacMstMnt_Model End

?>
