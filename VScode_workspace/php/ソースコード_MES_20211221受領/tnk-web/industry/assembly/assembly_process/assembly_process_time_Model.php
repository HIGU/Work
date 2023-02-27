<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����κ�ȹ��� (��ꡦ��λ����) ������       MVC Model ��            //
// Copyright (C) 2005-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Model.php                     //
// 2005/11/21 ���ϵ��� ���祳���ɤ˥�˥�����(537)�Ȼ��(534)���ɲ�         //
//            getViewEndList()�᥽�å� division by zero���б�(plan��0�ξ��)//
// 2005/11/30 userAdd()�᥽�åɤ�user�����å���work�Τߤ���time������å�   //
//            planAdd()�᥽�åɤ�@�ޡ����ηײ���������뵡ǽ�������ײ��  //
//              �ײ�ĤΥ����å���ǽ���ɲ�                                  //
//            planAdd_execute()�᥽�åɤηײ����ײ�Ŀ����ѹ�             //
//            getViewStartList(),getViewStartList(),getViewPlanListNotPage()//
//              �˷ײ�Ĥ��ɲä��ײ����Ǹ�ذ�ư(���֥륯��å��Ȳ���)    //
// 2005/12/01 checkWorkUser()�᥽�åɤ��ɲ� 10ʬ�вᤷ�������Ͻ�λ�ܥ����  //
//            ����˺���Ƚ�Ǥ���ư������롣getViewUserListNotPage()����ƽ�//
// 2005/12/07 getViewEndList()�᥽�åɤ˼��ͭ����̵����SQLʸ���ɲ�         //
// 2005/12/13 ��å����������� �и���� �� ��Ω���                         //
// 2005/12/15 �Խ����¤���������500���ɲ�(����Ĺ���б�)����¾�οͤ⤤�뤬�� //
// 2006/01/19 �ϰϤ� 60������ 62�����ѹ� ¾�ץ��������줹�뤿��         //
// 2006/04/06 �ȿ��ѹ��ˤ�� assemblyAuthUser() �᥽�åɤΥ��ƥʥ�      //
// 2006/05/18 �ɲä����ײ��ֹ����¸property���ɲä����ײ����Ͽ�����ν���  //
//            �᥽�å� outViewKousu() ���ɲ�                                //
// 2007/01/09 ǧ���ѥ᥽�åɤ��̸��¥ޥ������б����ѹ���getCheckAuthority //
// 2007/06/17 &$result �� $result ��(php5�б�) ��λ�ܥ���Ǽ�ʬ��work���� //
//            assyEnd()assyEndAll()�᥽�åɤ�userDelete_execute()���ɲ�     //
//            ����checkWorkUser()�᥽�åɤ�10ʬ��5ʬ���ѹ�                  //
//            ��Ω��λͽ���������ɲäΤ���outViewKousu()�᥽�åɤ��ѹ�      //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php');    // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*  ��Ω ���� ���� (��ꡦ��λ)���� ������ MVC��Model���� base class ���쥯�饹�����   *
*****************************************************************************************/
class AssemblyProcessTime_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $addPlanNo = '';                    // �ײ���ɲä����������ײ��ֹ�
    private $authDiv = 17;                      // ���Υӥ��ͥ����å��θ��¶�ʬ
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        switch ($request->get('showMenu')) {
        case 'EndList':
            $where  = "WHERE end_time <= '" . date('Ymd 235959') . "' ";
            $where .= "AND end_time >= (timestamp '" . date('Ymd 000000') . "' - interval '62 day') ";
            if ($request->get('group_no') != '') {
                $where .= "AND group_no = " . $request->get('group_no');
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'apend':
            ///// �ײ��ֹ����Ͽ���Τ�
            if ($request->get('group_no') != '') {
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('group_no');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'group':
            $where = '';
            $sql_sum = "
                SELECT count(*) FROM assembly_process_group
            ";
            break;
        case 'StartList':
        default:
            if ($request->get('group_no') != '') {
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('group_no');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
        }
        $log_file = 'assembly_process_time.log';
        $this->where = $where;
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, $log_file);
    }
    
    ////////// Operator�θ��¥����å� & ��Ω��ȼԤ�̾���򸡺����֤�
    public function getAuthorityUserName($user_id='')
    {
        if ($this->assemblyAuthUser()) {
            if ($user_id != '') {
                ///// user_id��Ŭ�������å�
                return $this->checkUserID($user_id);
            } else {
                return true;    // User_id�����ꤵ��Ƥʤ���� Authority Check �Τߤǥ꥿����
            }
        } else {
            $_SESSION['s_sysmsg'] = '��Ω�ؼ���˥塼���Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// ��Ω���κ�ȼ� ��Ͽ (work�ɲ�)
    public function userAdd($group_no, $user_id)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName($user_id)) ) {
            return false;
        }
        $chk_sql = "select plan_no from assembly_process_work where group_no={$group_no} and user_id='{$user_id}' limit 1";
        if ($this->getUniResult($chk_sql, $check) > 0) {    // group_no user_id ��(��ʣ)�Υ����å�
            $_SESSION['s_sysmsg'] = "{$userName} ����ϴ������Ϥ���Ƥ��ޤ���";
        } else {
            $chk_sql = "select plan_no from assembly_process_time where end_time='1970-01-01 00:00:00' AND group_no={$group_no} AND user_id='{$user_id}' limit 1";
            if ($this->getUniResult($chk_sql, $check) > 0) {    // end_time group_no user_id ��(��ʣ)�Υ����å�
                $_SESSION['s_sysmsg'] = "{$userName} ����ϴ��� �ײ��ֹ� {$check} �����Ϥ���Ƥ��ޤ���";
            } else {
                $response = $this->userAdd_execute($group_no, $user_id);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "{$userName} �������Ͽ�Ǥ��ޤ���Ǥ�����";
                }
            }
        }
        return false;
    }
    
    ////////// ��Ω���κ�ȼ� ��Ͽ�μ�� (work���)
    public function userDelete($group_no, $user_id)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName($user_id)) ) {
            return false;
        }
        $chk_sql = "select plan_no from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // ��λ������(���ؼ�ʬ)����Ͽ����Ƥ��뤫��
            $_SESSION['s_sysmsg'] = "{$user_id} �����¾�οͤ��ѹ�����ޤ�����";
        } else {
            $response = $this->userDelete_execute($group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "{$userName} �������Ω���μ�ä��Ǥ��ޤ���Ǥ�����";
            }
        }
        return false;
    }
    
    ////////// ��Ω���ηײ��ֹ� ��Ͽ (work��user���Ƥ�ײ��ֹ�������time���ɲ�)
    public function planAdd($group_no, $plan_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        ///// �ײ��ֹ��Ŭ�������å��ȷײ�Ĥ����뤫�����å�
        $chk = "select plan - cut_plan - kansei from assembly_schedule where plan_no='{$plan_no}'";
        if ($this->getUniResult($chk, $check) <= 0) {   // �ײ��ֹ椬��Ͽ����Ƥ��뤫
            $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} �����Ĥ���ޤ���";
            return false;
        } else {
            if (substr($plan_no, 0, 1) == '@' && $check <= 0) {
                $sei_no = substr($plan_no, 1, 7);
                $query = "SELECT order_q, utikiri, nyuko FROM order_plan WHERE sei_no={$sei_no} limit 1";
                $order = array();
                if ($this->getResult2($query, $order) > 0) {   // ��¤�ֹ��ȯ���������å�
                    $order_q = $order[0][0]; $utikiri = $order[0][1]; $nyuko = $order[0][2];
                    $update_sql = "UPDATE assembly_schedule SET plan={$order_q}, cut_plan={$utikiri}, kansei={$nyuko} WHERE plan_no='{$plan_no}'";
                    $this->execute_Update($update_sql);
                    if ( ($order_q - $utikiri - $nyuko) <= 0 ) {
                        $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} �Ϸײ�Ĥ�����ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
                        return false;
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} �����Ĥ���ޤ��� ����ô���Ԥ�Ϣ���Ʋ�������";
                    return false;
                }
            } elseif ($check <= 0) {
                $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} �Ϸײ�Ĥ�����ޤ���";
                return false;
            }
        }
        $chk_sql1 = "select user_id from assembly_process_work where group_no={$group_no} limit 1";
        $res = array();
        if (($rows=$this->getResult2($chk_sql1, $res)) <= 0) {    // user_id plan_no��̤��λ����(��ʣ)�Υ����å�
            $_SESSION['s_sysmsg'] = "��ȼԤ���Ͽ������ޤ��� ��˺�ȼԤ���Ͽ�򤷤Ʋ�������";
            return false;
        }
        for ($i=0; $i<$rows; $i++) {
            $chk_sql2 = "
                select trim(name) from assembly_process_time LEFT OUTER JOIN user_detailes ON (user_id=uid)
                where end_time='19700101 000000' and group_no={$group_no} and user_id='{$res[$i][0]}' and plan_no='{$plan_no}'
            ";
            if ($this->getUniResult($chk_sql2, $name) > 0) {    // user_id plan_no��̤��λ����(��ʣ)�Υ����å�
                $_SESSION['s_sysmsg'] = "{$name}�����Ʊ�ײ�[{$plan_no}]�Ǵ�����ꤷ�Ƥ��ޤ�����ȼԤ���Ͽ���� ���� ��λ���Ʋ�������";
                return false;
            }
        }
        $response = $this->planAdd_execute($group_no, $plan_no);
        if ($response) {
            $this->addPlanNo = $plan_no;    // 2006/05/18 �ɲ�
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
        }
        return false;
    }
    
    ////////// ��Ω���ηײ��ֹ�μ�� (plan_no��NULL��UPDATE) ��Ω���ηײ��ֹ�(�桼������ʣ������) ���
    public function planDelete($serial_no, $plan_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $chk_sql = "
            select group_no, user_id
            from assembly_process_time where serial_no={$serial_no} and end_time='19700101 000000'
        ";
        $res = array();
        if ($this->getResult2($chk_sql, $res) < 1) {     // ��λ������(���ؼ�ʬ)����Ͽ����Ƥ��뤫��
            $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} ��¾�οͤ��ѹ�����ޤ�����";
        } else {
            $group_no = $res[0][0];
            $user_id  = $res[0][1];
            $response = $this->planDelete_execute($serial_no, $group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '�ײ��ֹ桧{$plan_no} �μ�ä��Ǥ��ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ��Ω�������Ͻ�λ���� (����Υ��롼���ֹ�� work �ơ��֥�쥳���ɤ�������)
    public function apendEnd($group_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT user_id FROM assembly_process_work WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $chk) > 0) {  // work�˥ǡ��������뤫��
            $save_sql   = "
                SELECT * FROM assembly_process_work WHERE group_no={$group_no}
            ";
            $delete_sql = "
                DELETE FROM assembly_process_work WHERE group_no={$group_no}
            ";
            $response = $this->execute_Delete($delete_sql, $save_sql);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '���Ͻ�λ�������Ǥ��ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ��Ω��λ������ (����) serial_no�Ǿ�����Ф� 1��ȼԤ����Ƥηײ��ֹ��λ
    public function assyEnd($serial_no, $plan_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT str_time, group_no, user_id
            FROM assembly_process_time WHERE serial_no={$serial_no} AND end_time='19700101 000000'
        ";
        $res = array(); // �����
        if ($this->getResult2($query, $res) > 0) {  // ��λ��������serial_no����Ͽ����Ƥ��뤫��
            $str_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->assyEnd_execute($str_time, $group_no, $user_id);
            if ($response) {
                $this->userDelete_execute($group_no, $user_id); // 2007/06/17 ����ȼ԰����˻ĤäƤ���к��
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��λ���Ϥ��Ǥ��ޤ���Ǥ�����';
            }
        } else {
            $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} ��¾�οͤ��ѹ�����ޤ�����";
        }
        return false;
    }
    
    ////////// ��Ω��λ������ (���) serial_no����str_time,group_no��plan_no��������ư�細λ��Ԥ�
    ////////// 2007/06/17 ���ߤϻ��Ѥ��Ƥ��ʤ�
    public function assyEndAll($serial_no, $plan_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT str_time, group_no, user_id
            FROM assembly_process_time WHERE serial_no={$serial_no} AND end_time='19700101 000000'
        ";
        $res = array(); // �����
        if ($this->getResult2($query, $res) > 0) {  // ��λ��������serial_no����Ͽ����Ƥ��뤫��
            $str_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->assyEndAll_execute($str_time, $group_no);
            if ($response) {
                $this->userDelete_execute($group_no, $user_id); // 2007/06/17 ����ȼ԰����˻ĤäƤ���к��
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��λ���Ϥ��Ǥ��ޤ���Ǥ�����';
            }
        } else {
            $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} ��¾�οͤ��ѹ�����ޤ�����";
        }
        return false;
    }
    
    ////////// ��Ω��λ�μ�� (��ȼ���) serial_no����end_time,group_no,user_id��������ƺ�ȼ���ΰ���ä�Ԥ�
    public function endCancel($serial_no, $plan_no)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            select end_time, group_no, user_id
            from assembly_process_time where serial_no={$serial_no}
        ";
        $res = array(); // �����
        if ($this->getResult2($query, $res) > 0) {  // ��λ���ϺѤ�serial_no����Ͽ����Ƥ��뤫��
            $end_time = $res[0][0];
            $group_no = $res[0][1];
            $user_id  = $res[0][2];
            $response = $this->endCancel_execute($end_time, $group_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��λ�μ�ä��Ǥ��ޤ���Ǥ�����';
            }
        } else {
            $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} ��¾�οͤ��ѹ�����ޤ�����";
        }
        return false;
    }
    
    ////////// ��Ω���롼��(��ȶ�) ��Ͽ���ѹ�
    public function groupEdit($group_no, $group_name, $div, $product, $active)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT group_no, group_name, product, div, active FROM assembly_process_group WHERE group_no={$group_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // ��Ω���롼��(��ȶ�) ��Ͽ
            $response = $this->groupInsert($group_no, $group_name, $div, $product, $active);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ����Ͽ���ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '���롼�פ���Ͽ������ޤ���Ǥ�����';
            }
        } else {
            // �ǡ������ѹ�����Ƥ��뤫�����å�
            if ($group_no == $res[0][0] && $group_name == $res[0][1] && $product == $res[0][2] && $div == $res[0][3]) {
                return true;
            }
            // ��Ω���롼��(��ȶ�) �ѹ�
            $response = $this->groupUpdate($group_no, $group_name, $div, $product);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ���ѹ����ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '���롼�פ��ѹ�������ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ��Ω���롼��(��ȶ�)�� ���
    public function groupOmit($group_no, $group_name)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM assembly_process_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} �κ���оݥǡ���������ޤ���";
        } else {
            $response = $this->groupDelete($group_no);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} �������ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ��������ޤ���Ǥ�����";
            }
        }
        return false;
    }
    
    ////////// ��Ω���롼��(��ȶ�)�� ͭ����̵��
    public function groupActive($group_no, $group_name)
    {
        ///// Operator �� authentication check & user_id��Ŭ�������å�
        if (!($userName=$this->getAuthorityUserName()) ) {
            return false;
        }
        $query = "
            SELECT active FROM assembly_process_group WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ���оݥǡ���������ޤ���";
        } else {
            // ������ last_date last_host ����Ͽ�����������
            // regdate=��ư��Ͽ
            $last_date = date('Y-m-d H:i:s');
            $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
            if ($active == 't') {
                $active = 'FALSE';
            } else {
                $active = 'TRUE';
            }
            $update_sql = "
                UPDATE assembly_process_group SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE group_no={$group_no}
            "; 
            return $this->execute_Update($update_sql);
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ��Ω��� ����ɽ
    public function getViewStartList($result)
    {
        ///// �ʲ��� AS assyuser �� AS user �ǥ��顼�ˤʤ�(ͽ���)�����Ĺ������
        $query = "SELECT plan_no        AS �ײ��ֹ�
                        ,parts_no       AS �����ֹ�
                        ,substr(midsc, 1, 20)
                                        AS ����̾
                        ,plan_pcs       AS �ײ�Ŀ�
                        ,user_id        AS �Ұ��ֹ�
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,serial_no      AS Ϣ��
                        ,plan           AS �ײ��           -- 08
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    ��Ω��λ ����ɽ
    public function getViewEndList($result)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�
                        ,parts_no       AS �����ֹ�
                        ,substr(midsc, 1, 20)
                                        AS ����̾
                        ,plan_pcs       AS �ײ�Ŀ�
                        ,user_id        AS �Ұ��ֹ�
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����
                        ,assy_time      AS ��׹���
                        -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                        ,serial_no      AS Ϣ��         --  9
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ���Ͼܺ�     -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ��λ�ܺ�     -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS ����         -- 12
                        ,plan           AS �ײ��       -- 13
                        ,CASE
                            WHEN CURRENT_DATE = CAST(end_time AS date)
                            THEN '���ͭ��'
                            ELSE '���̵��'
                        END             AS ���         -- 14
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    {$this->where}
                    ORDER BY
                        end_time DESC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    ��Ω��� ��ȼ� ���ϸ�γ�ǧ�� ����ɽ (�ڡ�������ʤ�)
    public function getViewUserListNotPage($group_no, $result)
    {
        // ���Ͻ�λ�ܥ���β���˺������å��᥽�åɤθƽ�
        $this->checkWorkUser();
        if ($group_no != '') $where = "WHERE group_no={$group_no}"; else $where = '';
        $query = "
            SELECT user_id      AS �Ұ��ֹ�
                ,CASE
                    WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                    THEN '�����' || substr(user_id, 4, 3)
                    ELSE trim(assyuser.name)
                 END            AS ��ȼ�
                ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                AS ��������
                ,group_no       AS ���롼���ֹ�
            FROM
                assembly_process_work
            LEFT OUTER JOIN
                user_detailes   AS assyuser ON (user_id=uid)
            {$where}
            ORDER BY
                str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
            // $this->log_writer($query);   // debug��
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    ��Ω��� �ײ��ֹ� ���ϸ�γ�ǧ�� ����ɽ (�ڡ�������ʤ�)
    public function getViewPlanListNotPage($result)
    {
        $query = "
            SELECT plan_no      AS �ײ��ֹ�
                ,parts_no       AS �����ֹ�
                ,substr(midsc, 1, 20)
                                AS ����̾
                ,plan_pcs       AS �ײ�Ŀ�
                ,user_id        AS �Ұ��ֹ�
                ,CASE
                    WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                    THEN '�����' || substr(user_id, 4, 3)
                    ELSE trim(assyuser.name)
                 END            AS ��ȼ�
                ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                AS ��������
                ,serial_no      AS Ϣ��
                ,plan           AS �ײ��           -- 08
            FROM
                assembly_process_time
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            LEFT OUTER JOIN
                miitem ON (parts_no=mipn)
            LEFT OUTER JOIN
                user_detailes   AS assyuser ON (user_id=uid)
            {$this->where}
            ORDER BY
                str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
            // $this->log_writer($query);   // debug��
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    ��Ω���롼��(��ȶ�) ��Ͽ���� ����ɽ
    public function getViewGroupList($result)
    {
        $query = "SELECT group_no           AS ���롼���ֹ�
                        ,group_name         AS ���롼��̾
                        ,CASE
                            WHEN div = 'C' THEN '���ץ�'
                            WHEN div = 'L' THEN '��˥�'
                            ELSE '̤��Ͽ'
                         END                AS ������
                        ,CASE
                            WHEN product = 'C' THEN '���ץ�ɸ��'
                            WHEN product = 'S' THEN '���ץ�����'
                            WHEN product = 'L' THEN '��˥�����'
                            WHEN product = 'B' THEN '���Υݥ��'
                            ELSE '̤��Ͽ'
                         END                AS ���ʥ��롼��
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                            AS �ѹ�����
                        ,CASE
                            WHEN active THEN 'ͭ��'
                            ELSE '̵��'
                         END                AS ͭ��̵��
                        ,div
                        ,product
                    FROM
                        assembly_process_group
                    ORDER BY
                        group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ��Ω���롼��(��ȶ�) ̾�Τ��֤�
    public function getGroupName($group_no)
    {
        $query = "
            SELECT
                group_name     AS ���롼��̾
            FROM
                assembly_process_group
            WHERE
                group_no = {$group_no}
        ";
        $res = '̤��Ͽ';
        $this->getUniResult($query, $res);
        return $res;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
    public function getViewDataEdit($serial_no, $result)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�
                        ,parts_no       AS �����ֹ�
                        ,substr(midsc, 1, 20)
                                        AS ����̾
                        ,plan           AS �ײ��
                        ,user_id        AS �Ұ��ֹ�
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����
                        ,serial_no      AS Ϣ��
                    FROM
                        assembly_process_time
                    LEFT OUTER JOIN
                        assembly_schedule USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    LEFT OUTER JOIN
                        user_detailes   AS assyuser ON (user_id=uid)
                    WHERE
                        serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('plan_no',    $res[0][1]);
            $result->add_once('assy_no',    $res[0][2]);
            $result->add_once('assy_name',  $res[0][3]);
            $result->add_once('user_id',    $res[0][4]);
            $result->add_once('user_name',  $res[0][5]);
            $result->add_once('str_time',   $res[0][6]);
            $result->add_once('end_time',   $res[0][7]);
            $result->add_once('serial_no',  $res[0][7]);
        }
        return $rows;
    }
    
    ///// ��ꤷ���ײ��ֹ�ι����Ȳ�
    public function outViewKousu($menu)
    {
        if ($this->addPlanNo == '') return '';
        ///// ���ߤޤǤλ��ѹ��������
        $used_time = Uround($this->getUsedAssyTime($this->addPlanNo), 3);
        ///// �������Ǥκ�ȼԿ������
        $worker_count = $this->getWorkerCount($this->addPlanNo);
        $script = '';
        // $script .= "<script type='text/javascript'>\n";
        $script = "AssemblyProcessTime.win_open(\"{$menu->out_action('��Ͽ�����Ȳ�')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        // $script .= "</script>\n";
        //$script = "AssemblyProcessTime.win_openc(\"{$menu->out_action('��Ŭ������')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        return $script;
    }
    
    ///// ��ꤷ���ײ��ֹ�����ʤ��Ф�����Ŭ������Ȳ�
    public function outViewClame($menu)
    {
        if ($this->addPlanNo == '') return '';
        ///// ���ߤޤǤλ��ѹ��������
        $used_time = Uround($this->getUsedAssyTime($this->addPlanNo), 3);
        ///// �������Ǥκ�ȼԿ������
        $worker_count = $this->getWorkerCount($this->addPlanNo);
        $script = '';
        // $script .= "<script type='text/javascript'>\n";
        $script = "AssemblyProcessTime.win_openc(\"{$menu->out_action('��Ŭ������')}?noMenu=yes&regOnly=yes&targetPlanNo={$this->addPlanNo}&usedTime={$used_time}&workerCount={$worker_count}\", 900, 500);\n";
        // $script .= "</script>\n";
        return $script;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ��Ω�ؼ���˥塼���Խ����¥����å��᥽�å�(���ѥ᥽�å�)
    protected function assemblyAuthUser()
    {
        if ($this->getCheckAuthority($this->authDiv)) {
            return true;
        } else {
            return false;
        }
        
        ///// �ʲ��ϸ��߻��Ѥ��Ƥ��ʤ�
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // �Ұ��ν�°�������祳���ɤǥ����å�
            case 500:                   // ������ (2005/12/15�ɲ�)
            case 176:
            case 522:
            case 523:
            case 525:                   // ����
            case 514:                   // ���ץ���
                return true;            // ���ץ���Ω(�����������2006/04/06)
            case 551:
            case 175:
            case 560:
            case 537:                   // ��˥�����
            case 534:                   // ��˥����
                return true;            // ��˥���Ω(��ࡦ�������������2005/11/21)
            default:
                if ($_SESSION['Auth'] >= 3) { // �ƥ�����
                    return true;
                }
                return false;
            }
        } else {
            return false;
        }
    }
    ////////// ��Ω�ؼ���˥塼�λ���(�٤߻��֤����)�ι��(ʬ)���֤�
    protected function getSumTime($str_time, $end_time)
    {
        // ��׻���(ʬ)�����(�٤߻��֤������)
        // ��׹����Ͼ������ʲ�3�̤ޤǷ׻����롣(��Ω�����Τ���)
        $query = "
            SELECT
            Uround(CAST(extract(epoch from timestamp '{$end_time}' - timestamp '{$str_time}') / 60 AS NUMERIC), 3)
        ";
        $res = 0;
        $this->getUniResult($query, $res);
        $str_date = substr($str_time, 0, 10);
        $end_date = substr($end_time, 0, 10);
        // ī��Σ�ʬ
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 08:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 08:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // 10:30�Σ�ʬ
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 10:30:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 10:35:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 5;
        // ��٤ߤΣ���ʬ
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 12:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 12:45:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 45;
        // 15:00�Σ���ʬ
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 15:00:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 15:10:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 10;
        // 17:15�Σ���ʬ
        $query = "
            SELECT CASE WHEN timestamp '{$str_time}' <= timestamp '{$str_date} 17:15:00' THEN
            CASE WHEN timestamp '{$end_time}' >= timestamp '{$end_date} 17:30:00' THEN '1'
            ELSE '0' END ELSE '0' END ;
        ";
        $flg = '0';
        $this->getUniResult($query, $flg);
        if ($flg) $res -= 15;
        
        // ���顼�����å�
        if ($res < 0) $res = 0;
        return $res;
    }
    ////////// Ʊ�� �ײ��ֹ�ηײ���ι�פ�Ʒ׻�����������
    protected function plan_pcsUpdate($group_no, $user_id)
    {
        // Ʊ�� �ײ��ֹ�ι�׷ײ��(plan_pcs)����� Ʊ���ȼԤξ���Ʊ����Ω�ײ�ʬ�ȸ��ʤ�
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
        ";
        $plan_all_pcs = 0;     // �����
        $this->getUniResult($query, $plan_all_pcs);
        // ������Ʊ����Ω���ʬ��¾�ηײ褬�����plan_all_pcs��UPDATE����
        $query = "
            SELECT serial_no FROM assembly_process_time WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1��Ǥ⤢��� UPDATE ����
            $query = "
                UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
                WHERE end_time='19700101 000000' and group_no={$group_no} and user_id='{$user_id}'
            ";
            if (!$this->execute_Update($query)) {
                $_SESSION['s_sysmsg'] = 'Ʊ����Ω�ײ�ʬ�ι�׷ײ�����ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return $plan_all_pcs;
    }
    ////////// ��Ω ��ȼԤ�user_id��Ŭ��������å�����å������ܷ��(��̾=OK,false=NG)���֤�
    protected function checkUserID($user_id)
    {
        ///// user_id��Ŭ�������å�
        $chk = "SELECT trim(name) FROM user_detailes WHERE uid='{$user_id}'";
        if ($this->getUniResult($chk, $user_name) <= 0) {   // �Ұ���Ͽ����Ƥ��뤫
            if ($user_id < 777001 || $user_id > 777999) {   // �׻�(����)�Ǥʤ����
                $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$user_id} �������Ǥ���";
            } else {
                return ('�����' . substr($user_id, 3, 3) );
            }
        } else {
            return $user_name;
        }
        return false;
    }
    
    ////////// ��Ω���ϻؼ������Ͻ�λ�ܥ���β���˺������å� �� �桼�������ꥢ
    protected function checkWorkUser()
    {
        // 10ʬ�ʾ�вᤷ�Ƥ���桼���������뤫��
        $query = "
            SELECT * FROM assembly_process_work WHERE str_time <= (CURRENT_TIMESTAMP - interval '5 minute')
        ";
        if ($this->getResult2($query, $res) > 0) {
            $delete_sql = "
                DELETE FROM assembly_process_work WHERE str_time <= (CURRENT_TIMESTAMP - interval '5 minute')
            ";
            // $save_sql �� $query �ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
            $result_flg = $this->execute_Delete($delete_sql, $query);
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// ��Ω���� User������ (work���ɲ�)
    private function userAdd_execute($group_no, $user_id)
    {
        // Ʊ����Ω�ײ�ν��ײ褫��
        $query = "
            SELECT str_time FROM assembly_process_work
            WHERE group_no={$group_no} and user_id='{$user_id}'
            LIMIT 1
        ";
        if ($this->getUniResult($query, $str_time) <= 0) {
            // ���ײ�Τ�����֤����ꤹ��
            $str_time = date('Y-m-d H:i:s');
        }
        
        // ��ȼԤ���Ͽ�¹� (work����Ͽ)
        $insert_qry = "
            insert into assembly_process_work
            (group_no, user_id, str_time)
            values
            ($group_no, '$user_id', '$str_time')
        ";
        $result_flg = $this->execute_Insert($insert_qry);
        return $result_flg;
    }
    
    ////////// ��Ω���κ�ȼ� ��� (work�������)
    private function userDelete_execute($group_no, $user_id)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        $delete_sql = "delete from assembly_process_work where group_no={$group_no} and user_id='{$user_id}'";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        return $result_flg;
    }
    
    ////////// ��Ω���� �ײ��ֹ������ (�ɲ�)
    private function planAdd_execute($group_no, $plan_no)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // ���ηײ�(plan_no)�ηײ��(plan)���ײ�Ŀ� �����
        $query = "
            SELECT plan - cut_plan - kansei FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $plan = 0;     // �����
        $this->getUniResult($query, $plan);
        
        // �����оݤκ�ȼԤ����
        $query = "
            SELECT user_id, str_time FROM assembly_process_work WHERE group_no={$group_no}
        ";
        $res = array(); // �����
        $rows = $this->getResult2($query, $res);
        
        // ��Ͽ�¹� (���λ����Ǥ�plan_all_pcs��parts_pcs��Ʊ���ˤ���)
        $end_time = '19700101 000000';
        for ($i=0; $i<$rows; $i++) {
            $insert_qry = "
                insert into assembly_process_time
                (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, last_date, last_host)
                values
                ($group_no, '$plan_no', '{$res[$i][0]}', '{$res[$i][1]}', '$end_time', $plan, $plan, '$last_date', '$last_host')
            ";
            $result_flg = $this->execute_Insert($insert_qry);
            
            // Ʊ����Ωʬ��plan_all_pcs�򹹿�
            if ($result_flg) {
                $this->plan_pcsUpdate($group_no, $res[$i][0]);
            }
        }
        return $result_flg;
    }
    
    ////////// ��Ω���ηײ��ֹ� ��� (�������) ���̺��
    private function planDelete_execute($serial_no, $group_no, $user_id)
    {
        // ��¸�Ѥ�SQLʸ������ user_id��̵���Ƥ��ɤ���debug�������Ω��
        $save_sql   = "select * from assembly_process_time where serial_no={$serial_no} and user_id='{$user_id}'";
        $delete_sql = "delete from assembly_process_time where serial_no={$serial_no} and user_id='{$user_id}'";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        
        // Ʊ���и�ʬ��plan_pcs�򹹿�
        if ($result_flg) {
            $this->plan_pcsUpdate($group_no, $user_id);
        }
        return $result_flg;
    }
    
    ////////// �������Ǥλ��� �ײ��ֹ�Ǥκ�ȼԿ������
    private function getWorkerCount($planNo)
    {
        $query = "
            SELECT assy_time, plan_pcs, plan_all_pcs, str_time FROM assembly_process_time
            WHERE plan_no='{$planNo}' AND assy_time IS NULL AND end_time='19700101 000000'
        ";
        return $this->getResult2($query, $res);
    }
    
    ////////// ���ߤޤǤλ��� �ײ��ֹ�Ǥλ��ѹ�������� (��Ω��λ���Υ��å���Ʊ��)
    private function getUsedAssyTime($planNo)
    {
        $end_time = date('Y-m-d H:i:s');    // ���ߤ������򥻥å�
        $query = "
            SELECT assy_time, plan_pcs, plan_all_pcs, str_time FROM assembly_process_time
            WHERE plan_no='{$planNo}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return 0;
        $sum_assy_time = 0;
        for ($i=0; $i<$rows; $i++) {
            $assy_time    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $str_time     = $res[$i][3];
            if (!$assy_time) {
                $sum_time = $this->getSumTime($str_time, $end_time);    // �٤߻��֤��������׹���(ʬ)�����
                $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            }
            $sum_assy_time += $assy_time;
        }
        return $sum_assy_time;
    }
    
    ////////// ��Ω��λ������ (�ѹ�) ����(1��ȼԤ����Ƥηײ��ֹ��λ)
    private function assyEnd_execute($str_time, $group_no, $user_id)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $end_time = date('Y-m-d H:i:s');
        $sum_time = $this->getSumTime($str_time, $end_time);    // �٤߻��֤��������׹���(ʬ)�����
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE str_time='{$str_time}' AND group_no={$group_no} AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
    ////////// ��Ω��λ������ (�ѹ�) ��細λ(���Ƥκ�ȼԤ����Ƥηײ��ֹ��λ)
    private function assyEndAll_execute($str_time, $group_no)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $end_time = date('Y-m-d H:i:s');
        $sum_time = $this->getSumTime($str_time, $end_time);    // �٤߻��֤��������׹���(ʬ)�����
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE str_time='{$str_time}' AND group_no={$group_no} AND end_time='19700101 000000'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
    ////////// ��Ω��λ�μ�� (�ѹ�) ��ȼ���ΰ����(��ȼԤ����Ƥηײ��ֹ����)
    private function endCancel_execute($end_time, $group_no, $user_id)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // serial_no�ǻ��ꤵ�줿 end_time, group_no, user_id ��Ʊ����λ�ײ�ʬ���������
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE end_time='{$end_time}' AND group_no={$group_no} AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no = $res[$i][0];
            // ��¸�Ѥ�SQLʸ������
            $save_sql = "SELECT * FROM assembly_process_time WHERE serial_no={$res[$i][0]}";
            $update_sql = "
                UPDATE assembly_process_time SET
                end_time='19700101 000000', assy_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
            $this->execute_Update($update_sql, $save_sql);
        }
        return true;
    }
    
    ////////// ��Ω���롼��(��ȶ�)����Ͽ (�¹���)
    private function groupInsert($group_no, $group_name, $product, $div, $active)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_group
            (group_no, group_name, product, div, active, last_date, last_host)
            VALUES
            ($group_no, '$group_name', '$product', '$div', '$active', '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ��Ω���롼��(��ȶ�)���ѹ� (�¹���)
    private function groupUpdate($group_no, $group_name, $div, $product)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $update_sql = "
            UPDATE assembly_process_group SET
            group_no={$group_no}, group_name='{$group_name}', product='{$product}', div='{$div}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE group_no={$group_no}
        "; 
        return $this->execute_Update($update_sql);
    }
    
    ////////// ��Ω���롼��(��ȶ�)�κ�� (�¹���)
    private function groupDelete($group_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "SELECT * FROM assembly_process_group WHERE group_no={$group_no}";
        // �����SQLʸ������
        $delete_sql = "DELETE FROM assembly_process_group WHERE group_no={$group_no}";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ��Ω��λ�����ϡ���� (�ѹ�) �ʲ��ϥǥХå��Ѥ˻Ĥ��Ƥ���
    private function chg_execute($status, $serial_no, $user_id, $str_time)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // status �򸫤ƴ�λ���Ϥ���ä���Ƚ�Ǥ���
        if ($status == 'end') {             // ��λ����
            $end_time = date('Y-m-d H:i:s');
            $sum_time = $this->getSumTime($str_time, $end_time);    // �٤߻��֤��������׹���(ʬ)�����
            $query = "
                SELECT serial_no, plan_pcs, parts_pcs FROM assembly_process_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            // debug $_SESSION['sum_time'] = $sum_time;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                $plan_pcs  = $res[$i][1];
                $parts_pcs = $res[$i][2];
                // debug $_SESSION["assy_time$i"] = ($parts_pcs / $plan_pcs) * $sum_time;
                // debug $_SESSION["assy_round$i"] = round(($parts_pcs / $plan_pcs) * $sum_time, 0);
                $assy_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
                $update_sql = "
                    UPDATE assembly_process_time SET
                    end_time='{$end_time}', assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                $this->execute_Update($update_sql);
            }
        } elseif ($status == 'cancel') {     // ��λ�μ��
            // serial_no�ǻ��ꤵ�줿 user_id��end_time ��Ʊ����λ�ײ�ʬ���������
            $query = "
                SELECT serial_no FROM assembly_process_time
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                // ��¸�Ѥ�SQLʸ������
                $save_sql = "SELECT * FROM assembly_process_time WHERE serial_no={$res[$i][0]}";
                $update_sql = "
                    UPDATE assembly_process_time SET
                    end_time='19700101 000000', assy_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
                $this->execute_Update($update_sql, $save_sql);
            }
        }
        return true;
    }
    
} // Class AssemblyProcessTime_Model End

?>
