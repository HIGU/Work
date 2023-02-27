<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ��ޥ������Υ᡼�륢�ɥ쥹 �Ȳ񡦥��ƥʥ�                          //
//                                                            MVC Model ��  //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_Model.php                               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class


/******************************************************************************
*     �ǹ礻(���)�������塼���� MVC��Model�� base class ���쥯�饹�����     *
******************************************************************************/
class mailAddress_Model extends ComTableMnt
{
    ///// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        switch ($request->get('condition')) {
        case 'taishoku':
            $this->where = "WHERE uid != '000000' AND retire_date IS NOT NULL";
            break;
        case 'syukko':
            $this->where = "WHERE uid != '000000' AND sid = 31 AND retire_date IS NULL";
            break;
        case 'ALL':
            $this->where = "WHERE uid != '000000'";
            break;
        case 'genzai':
        default:
            $this->where = "WHERE uid != '000000' AND retire_date IS NULL AND sid != 31";
            break;
        }
        $sql_sum = "
            SELECT count(*) FROM user_master LEFT OUTER JOIN user_detailes USING(uid) {$this->where}
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'mail/mailAddress.log', 15);    // 1�ǥ쥳���ɿ��ν����=15�����
    }
    
    ////////// �᡼�륢�ɥ쥹����Ͽ���ѹ� (�ºݤˤϤ�������Ͽ�Ϥ��ʤ�)
    public function mail_edit($uid, $mailaddr)
    {
        ///// �Խ����¤Υ����å�
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uid��Ŭ�������å�
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT uid, mailaddr FROM user_master WHERE uid='{$uid}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '���ߤ��Υ�˥塼�ǥ��ɥ쥹����Ͽ�϶ػߤ��Ƥ��ޤ���';
            return false;
            // ���ɥ쥹����Ͽ
            $response = $this->mailInsert($uid, $mailaddr);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹 [ {$mailaddr} ] ����Ͽ���ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹����Ͽ������ޤ���Ǥ�����";
            }
        } else {
            // ���ɥ쥹���ѹ�
            // �ǡ������ѹ�����Ƥ��뤫�����å�
            if ($uid == $res[0][0] && $mailaddr == $res[0][1]) return true;
            // ���ɥ쥹���ѹ� �¹�
            $response = $this->mailUpdate($uid, $mailaddr);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹�� [ {$mailaddr} ] ���ѹ����ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹���ѹ�������ޤ���Ǥ�����";
            }
        }
        return false;
    }
    
    ////////// ��ļ��� ���
    public function mail_omit($uid, $mailaddr)
    {
        ///// �Խ����¤Υ����å�
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uid��Ŭ�������å�
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT uid, mailaddr FROM user_master WHERE uid='{$uid}'
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[ {$name} ] ����� [{$uid}] {$mailaddr} �Ϻ���оݥǡ���������ޤ���";
        } else {
            ///// ������Ƥ�����ʤ������Υǡ���������å�(�����user_detailes�ǥ����å�)
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid='{$uid}';
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->mailDelete($uid);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹 [ {$mailaddr} ] �������ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[ {$name} ] ����Υ᡼�륢�ɥ쥹 {$mailaddr} ��������ޤ���Ǥ�����";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$uid}] {$mailaddr} �ϸ��߻�����Ǥ�������Ǥ��ޤ���";
            }
        }
        return false;
    }
    
    ////////// �᡼�륢�ɥ쥹�� ͭ����̵�� (���󤳤Υ᥽�åɤϻ��Ѥ��ʤ�)
    public function mail_activeSwitch($uid, $mailaddr)
    {
        ///// �Խ����¤Υ����å�
        if (!($name=$this->checkAuth())) {
            return false;
        }
        ///// uid��Ŭ�������å�
        if (!($name=$this->checkUid($uid))) {
            return false;
        }
        $query = "
            SELECT active FROM user_master WHERE uid='{$uid}'
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$uid}] {$mailaddr} ���оݥǡ���������ޤ���";
        } else {
            // ������ last_date last_host ����Ͽ�����������
            // regdate=��ư��Ͽ
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            // ��¸�Ѥ�SQLʸ������
            $save_sql = "
                SELECT active FROM user_master WHERE uid='{$uid}'
            ";
            $update_sql = "
                UPDATE user_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE uid='{$uid}'
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��
    public function getViewMailList(&$result)
    {
        $query = "
            SELECT master.uid                           -- 00
                ,trim(name)                             -- 01
                ,trim(master.mailaddr)                  -- 02
                ,CASE
                    WHEN master.last_date IS NULL THEN '01/07/01 08:30'
                    ELSE to_char(master.last_date, 'YY/MM/DD HH24:MI')
                 END                                    -- 03
            FROM
                user_master AS master
            LEFT OUTER JOIN
                user_detailes USING(uid)
            {$this->where}
            ORDER BY
                pid DESC, sid ASC, master.uid ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �Խ����¤�����å����Ʒ�̤��֤� (false=�Ȳ�Τ�, true=�Խ�OK)
    protected function checkAuth()
    {
        ///// Auth��Ŭ�������å�
        if ($_SESSION['Auth'] >= 2) {
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '�Խ����¤�����ޤ��� �Ȳ�Τ߹ԤäƲ�������';
            return false;
        }
    }
    
    ////////// �᡼�륢�ɥ쥹��uid��Ŭ��������å�����å������ܷ��(true=̾��,false=NG)���֤�
    protected function checkUid($uid)
    {
        ///// uid��Ŭ�������å� (user_detailes����Ͽ�����뤫)
        $query = "
            SELECT trim(name) FROM user_detailes WHERE uid = '{$uid}'
        ";
        if ($this->getUniResult($query, $name) > 0) {
            return $name;
        } else {
            $_SESSION['s_sysmsg'] = "[ {$uid} ] ��̵���ʼҰ��ֹ�Ǥ���";
        }
        return false;
    }
    
    ////////// �᡼�륢�ɥ쥹����Ͽ (�¹���) (����ϻ��Ѥ��ʤ�)
    protected function mailInsert($uid, $mailaddr)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        // $last_date = date('Y-m-d H:i:s');
        // $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $insert_sql = "
            INSERT INTO user_master
            (uid, mailaddr, last_date, last_host)
            VALUES
            ('$uid', '$mailaddr', '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// �᡼�륢�ɥ쥹���ѹ� (�¹���)
    protected function mailUpdate($uid, $mailaddr)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        // $last_date = date('Y-m-d H:i:s');
        // $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT uid, mailaddr, last_date, last_user FROM user_master WHERE uid='{$uid}'
        ";
        $update_sql = "
            UPDATE user_master SET
            mailaddr='{$mailaddr}'
            WHERE uid='{$uid}'
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �᡼�륢�ɥ쥹�κ�� (�¹���)
    protected function mailDelete($uid)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM user_master WHERE uid='{$uid}'
        ";
        // �����SQLʸ������
        $delete_sql = "
            DELETE FROM user_master WHERE uid='{$uid}'
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
} // Class mailAddress_Model End

?>
