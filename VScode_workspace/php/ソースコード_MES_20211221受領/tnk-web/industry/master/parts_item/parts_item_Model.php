<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ƥ�����ʡ����ʴط��Υ����ƥ�ޥ������ξȲ񡦥��� MVC Model ��//
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Model.php                                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php');// TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*       ���������ƥ�����ʡ����ʤΥ����ƥ�ޥ����� MVC��Model���� ��ĥ���饹�����       *
*****************************************************************************************/
class PartsItem_Model extends ComTableMnt
{
    ///// Private properties
    private $partsKey = '';                         // �����ե������
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $partsKey='')
    {
        if ($partsKey == '') {
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            $this->partsKey = $partsKey;    // Properties�ؤ���Ͽ
        }
        $sql_sum = "
            SELECT count(*) FROM miitem where mipn like '{$partsKey}%'
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'parts_item_Master.log');
    }
    
    ////////// �ޥ������ɲ�
    public function table_add($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg='')
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql1 = "select mipn from miitem where mipn='{$parts_no}' limit 1";
            if ($this->getUniResult($chk_sql1, $check) > 0) {    // parts_no����Ͽ�ѤߤΥ����å�
                $_SESSION['s_sysmsg'] = "���ʡ����� �ֹ桧{$parts_no} �ϴ�����Ͽ����Ƥ��ޤ�";
            } else {
                if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                $response = $this->add_execute($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '�����ط��Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ����� �ѹ�
    public function table_change($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg='')
    {
        if ($this->IndustAuthUser('MASTER')) {
            $query = "select mipn from miitem where mipn='{$preParts_no}'";
            if ($this->getUniResult($query, $check) > 0) {  // �ѹ����������ֹ椬��Ͽ����Ƥ��뤫��
                $chk_sql1 = "select mipn from miitem where mipn='{$parts_no}'";
                if ($preParts_no != $parts_no) {
                    if ($this->getUniResult($chk_sql1, $check) > 0) {    // �ѹ���������ֹ椬������Ͽ����Ƥ��뤫��
                        $_SESSION['s_sysmsg'] = "���ʡ����� �ֹ桧{$parts_no} �ϴ�����Ͽ����Ƥ��ޤ���";
                    } else {
                        if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                        $response = $this->chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                        if ($response) {
                            return true;
                        } else {
                            $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                        }
                    }
                } else {
                    if ( !($partsASReg = $this->ASRegCheck($partsASReg)) ) return false;
                    $response = $this->chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] = "���ʡ����� �ֹ桧{$preParts_no} ��¾�οͤ��ѹ�����ޤ�����";
            }
        } else {
            $_SESSION['s_sysmsg'] = '�����ط��Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// �ޥ������δ������
    public function table_delete($parts_no)
    {
        if ($this->IndustAuthUser('MASTER')) {
            $chk_sql = "select mipn from miitem where mipn='{$parts_no}'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // parts_no��¸�ߥ����å�
                $_SESSION['s_sysmsg'] = "���ʡ����� �ֹ桧{$parts_no} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($parts_no);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '����Ǥ��ޤ���Ǥ�����';
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = '�����ط��Υޥ������Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��
    public function getViewDataList(&$result)
    {
        ///// ��� $partsKey �ե�����ɤǤθ���
        $query = "
            SELECT mipn         AS parts_no
                ,midsc          AS ̾��
                ,CASE
                    WHEN mzist='' THEN '&nbsp;'
                    WHEN mzist IS NULL THEN '&nbsp;'
                    ELSE mzist
                 END            AS ���
                ,CASE
                    WHEN mepnt='' THEN '&nbsp;'
                    WHEN mepnt IS NULL THEN '&nbsp;'
                    ELSE mepnt
                 END            AS �Ƶ���
                ,CASE
                    WHEN madat IS NULL THEN '&nbsp;'
                    ELSE to_char(madat, 'FM9999/99/99')
                 END            AS ��Ͽ��
            FROM
                miitem
            WHERE
                mipn like '{$this->partsKey}%'
            ORDER BY
                parts_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
    public function getViewDataEdit($parts_no, &$result)
    {
        $query = "
            SELECT mipn
                ,midsc
                ,mzist
                ,mepnt
                ,to_char(madat, 'FM9999/99/99')
            FROM
                miitem
            WHERE
                mipn = '{$parts_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('parts_name', $res[0][1]);
            $result->add_once('partsMate',  $res[0][2]);
            $result->add_once('partsParent',$res[0][3]);
            $result->add_once('partsASReg', $res[0][4]);
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
    private function add_execute($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg)
    {
        // ������ last_date last_user ����Ͽ�����������
        // regdate=��ư��Ͽ�� miitem�ˤϤʤ�
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $insert_qry = "
            insert into miitem
            (mipn, midsc, mzist, mepnt, madat, last_date, last_user)
            values
            ('$parts_no', '$parts_name', '$partsMate', '$partsParent', $partsASReg, '$last_date', '$last_user')
        ";
        return $this->execute_Insert($insert_qry);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� �ѹ�
    private function chg_execute($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "select * from miitem where mipn='{$preParts_no}'";
        // ������ last_date last_user ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SESSION['User_ID'];
        $update_sql = "
            UPDATE miitem SET
            mipn='{$parts_no}', midsc='{$parts_name}', mzist='{$partsMate}', mepnt='{$partsParent}', madat={$partsASReg}, last_date='{$last_date}', last_user='{$last_user}'
            WHERE mipn='{$preParts_no}'
        "; 
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����������Υ��󥿡��ե����� �ޥ����� ���(����)
    private function del_execute($parts_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from miitem where mipn='{$parts_no}'";
        $delete_sql = "delete from miitem where mipn='{$parts_no}'";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// AS/400 ����Ͽ�����Խ��������Υ��顼�����å��᥽�å�
    private function ASRegCheck($partsASReg)
    {
        if ($partsASReg == '') {
            $partsASReg = date('Ymd');
        } else {
            $partsASReg = str_replace('/', '', $partsASReg);    // 2005/09/13 �� 20050913 ��
            if ($partsASReg > date('Ymd') || $partsASReg < 19700101) {
                $_SESSION['s_sysmsg'] = 'AS��Ͽ�����۾��ͤǤ��� ��Ͽ����ޤ���';
                return false;
            }
        }
        return $partsASReg;
    }
    
} // Class EquipMacMstMnt_Model End

?>
