<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC Model ��                   //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_linear_Model.php                       //
// 2005/09/27 WHERE end_time IS NULL �Ǥϥ���ǥå������Ȥ��ʤ�����         //
//            NULL ���Ѥ��� '19700101 000000' ����Ѥ���̤��λ��ɽ��      //
//            �и˴�λ������ interval '60 day' �� 60�����ޤ�ɽ��������ɲ�  //
//            [���¤��ʤ�]��å���������Ͽ������ޤ���Υ�å����������֥�  //
//            .= '��Ͽ������ޤ���' ���б���.=��                          //
// 2005/10/04 �и˺�ȼԤ���Ͽ�ơ��֥��ͭ����̵�����ɲ�  ȼ���᥽�å��ɲ�  //
// 2005/10/07 ��ȼԻؼ��ܥ���Υǡ��������˥ڡ�������򤷤ʤ��᥽�åɤ����//
// 2005/10/13 '�ײ��ֹ桧{$plan_no}'��"�ײ��ֹ桧{$plan_no}" �����ץߥ����� //
// 2005/12/08 getViewDataEndList()�᥽�åɤ˼��ͭ����̵����SQLʸ���ɲ�     //
// 2005/12/10 ��ꡦ��λ���֤ν����ѥ᥽�å� timeEdit_execute() ���ɲ�      //
// 2005/12/15 �Խ����¤���������500���ɲ�(����Ĺ���б�)����¾�οͤ⤤�뤬�� //
// 2006/01/18 table_add()�᥽�åɤ�@�ޡ����ηײ���������뵡ǽ�������ײ��//
//            �ײ�ĤΥ����å���ǽ���ɲ� ��Ω�ǡ������פ�Ʊ�����å����ɲ� //
// 2006/04/05 pickupAuthUser()�᥽�åɤ�������Τ�OK�� ��Ω���Τ��ѹ�     //
// 2006/06/06 parts_pickup_time �� parts_pickup_linear ���ѹ�����˥��Ǻ��� //
//            �ơ��֥�̾��嵭��Ʊ�ͤ��ѹ� parts_pickup_linear ��������     //
// 2006/06/14 ����ܥ����ǡ����μ���������ɲ� �᥽�å� invoiceTOplan()     //
// 2006/06/15 ����ܥ���No.���ϻ��˼�ư�ײ����ϥ᥽�åɤ��ɲ�invoicePlanAdd //
// 2007/01/09 ǧ���ѥ᥽�åɤ��̸��¥ޥ������б����ѹ���getCheckAuthority //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php'); // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*  �����������ʽи� ��ꡦ��λ���� ������ MVC��Model���� base class ���쥯�饹�����   *
*****************************************************************************************/
class PartsPickupLinear_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $authDiv = 18;                      // ���Υӥ��ͥ����å��θ��¶�ʬ
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        // ����ܥ����ǡ����Υ����å��ȼ��
        $this->invoiceTOplan();
        
        switch ($request->get('current_menu')) {
        case 'EndList':
        case 'TimeEdit':
            $where = "
            WHERE end_time <= '" . date('Ymd 235959') .
            "' AND end_time >= (timestamp '" . date('Ymd 000000') . "' - interval '62 day')
            ";
            break;
        case 'apend':
            $where = "WHERE end_time='19700101 000000' AND " . "user_id = '" . $request->get('user_id') . "'";
            break;
        case 'user':
            $where = '';
            break;
        default:        // 'list'������
            $where = "WHERE end_time='19700101 000000'";
        }
        if ($request->get('current_menu') == 'user') {
            $sql_sum = "
                SELECT count(*) FROM parts_pickup_linear_user
            ";
            $log_file = 'parts_pickup_linear_user.log';
        } else {
            $sql_sum = "
                SELECT count(*) FROM parts_pickup_linear $where
            ";
            $log_file = 'parts_pickup_linear.log';
        }
        $this->where = $where;
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        if ($request->get('current_menu') == 'user') $page_rec = 15; else $page_rec = 20;
        parent::__construct($sql_sum, $request, $log_file, $page_rec);
    }
    
    ////////// �и� ��ȼԤ�̾���򸡺����֤�
    public function getUserName($user_id)
    {
        if ($this->pickupAuthUser()) {
            ///// user_id��Ŭ�������å�
            return $this->checkUserID($user_id);
        }
        return false;
    }
    
    ////////// �и��������� (�ɲ�)
    public function table_add($plan_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            ///// user_id��Ŭ�������å�
            if (!$this->checkUserID($user_id)) {
                return false;
            }
            ///// �ײ��ֹ��Ŭ�������å�
            $chk = "SELECT plan_no FROM assembly_schedule WHERE plan_no='{$plan_no}'";
            if ($this->getUniResult($chk, $check) <= 0) {   // �ײ��ֹ椬��Ͽ����Ƥ��뤫
                if (!$this->invoicePlanAdd($plan_no, $user_id)) {   // ����ܥ����ֹ�ˤ��Ϣ³�ײ�����
                    return false;
                }
                return true;
            } else {
                return $this->table_add_check($plan_no, $user_id);
            }
        }
        return false;
    }
    
    ////////// �и��������� (�ɲ�)
    public function table_add_check($plan_no, $user_id)
    {
        ///// �ײ��ֹ��Ŭ�������å�
        $chk = "SELECT plan - cut_plan - kansei FROM assembly_schedule WHERE plan_no='{$plan_no}'";
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
        $chk_sql = "select plan_no from parts_pickup_linear where end_time='19700101 000000' and user_id='{$user_id}' and plan_no='{$plan_no}' limit 1";
        if ($this->getUniResult($chk_sql, $check) > 0) {    // user_id plan_no��̤��λ����(��ʣ)�Υ����å�
            $_SESSION['s_sysmsg'] = "Ʊ���ȼԤ� �ײ��ֹ桧[{$plan_no}] �νи���꤬���˻ؼ�����Ƥ��ޤ���";
        } else {
            $response = $this->add_execute($plan_no, $user_id);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// �и����μ�� (�������)
    public function table_delete($serial_no, $plan_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            $chk_sql = "select plan_no from parts_pickup_linear where serial_no={$serial_no} and end_time='19700101 000000'";
            if ($this->getUniResult($chk_sql, $check) < 1) {     // ��λ������(���ؼ�ʬ)����Ͽ����Ƥ��뤫��
                $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} ��¾�οͤ��ѹ�����ޤ�����";
            } else {
                $response = $this->del_execute($serial_no, $user_id);
                if ($response) {
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$plan_no} �и����μ�ä��Ǥ��ޤ���Ǥ�����";
                }
            }
        }
        return false;
    }
    
    ////////// �и˴�λ�����ϡ���� (�ѹ�)
    public function table_change($status, $serial_no, $user_id)
    {
        if ($this->pickupAuthUser()) {
            if ($status == 'end') {     // �и˴�λ����
                $query = "select str_time from parts_pickup_linear where serial_no={$serial_no} and end_time='19700101 000000'";
                if ($this->getUniResult($query, $str_time) > 0) {  // ��λ��������serial_no����Ͽ����Ƥ��뤫��
                    $response = $this->chg_execute($status, $serial_no, $user_id, $str_time);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '��λ���Ϥ��Ǥ��ޤ���Ǥ�����';
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "Serial�ֹ桧{$serial_no} ��¾�οͤ��ѹ�����ޤ�����";
                }
            } elseif ($status == 'cancel') {     // �и˴�λ�μ��
                $query = "select str_time from parts_pickup_linear where serial_no={$serial_no} and end_time != '19700101 000000'";
                if ($this->getUniResult($query, $str_time) > 0) {  // ��λ���ϺѤ�serial_no����Ͽ����Ƥ��뤫��
                    $response = $this->chg_execute($status, $serial_no, $user_id, $str_time);
                    if ($response) {
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '��λ�μ�ä��Ǥ��ޤ���Ǥ�����';
                    }
                } else {
                    $_SESSION['s_sysmsg'] = "Serial�ֹ桧{$serial_no} ��¾�οͤ��ѹ�����ޤ�����";
                }
            }
        }
        return false;
    }
    
    ////////// �и� ��ȼԤ� ��Ͽ���ѹ�
    public function user_edit($user_id, $user_name)
    {
        ///// user_id��Ŭ�������å�
        if (!$this->checkUserID($user_id)) {
            return false;
        }
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT user_id, user_name FROM parts_pickup_linear_user WHERE user_id='{$user_id}'
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                // �и� ��ȼ� ��Ͽ
                $response = $this->user_insert($user_id, $user_name);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} �������Ͽ���ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '��ȼԤ���Ͽ������ޤ���Ǥ�����';
                }
            } else {
                // �ǡ������ѹ�����Ƥ��뤫�����å�
                if ($user_id == $res[0][0] && $user_name == $res[0][1]) return true;
                // �и� ��ȼ� �ѹ�
                $response = $this->user_update($user_id, $user_name);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} ������ѹ����ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '��ȼԤ��ѹ�������ޤ���Ǥ�����';
                }
            }
        }
        return false;
    }
    
    ////////// �и� ��ȼԤ� ͭ����̵��
    public function user_active($user_id, $user_name)
    {
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT active FROM parts_pickup_linear_user WHERE user_id='{$user_id}'
            ";
            if ($this->getUniResult($query, $active) <= 0) {
                $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} ������оݥǡ���������ޤ���";
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
                    UPDATE parts_pickup_linear_user SET
                    active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE user_id='{$user_id}'
                "; 
                return $this->execute_Update($update_sql);
            }
        }
        return false;
    }
    
    ////////// �и� ��ȼԤ� ���
    public function user_omit($user_id, $user_name)
    {
        ///// user_id��Ŭ�������å�
        if (!$this->checkUserID($user_id)) {
            return false;
        }
        if ($this->pickupAuthUser()) {
            $query = "
                SELECT user_id, user_name FROM parts_pickup_linear_user WHERE user_id='{$user_id}'
            ";
            if ($this->getResult2($query, $res) <= 0) {
                $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} ����κ���оݥǡ���������ޤ���";
            } else {
                $response = $this->user_delete($user_id);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} ����������ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$user_id}] {$user_name} �����������ޤ���Ǥ�����";
                }
            }
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �и���� ����ɽ
    public function getViewDataList(&$result)
    {
        ///// �ʲ��� AS pickuser �� AS user �ǥ��顼�ˤʤ�(ͽ���)�����Ĺ������
        $query = "SELECT plan_no        AS �ײ��ֹ�
                        ,parts_no       AS �����ֹ�
                        ,substr(midsc, 1, 20)
                                        AS ����̾
                        ,plan           AS �ײ��
                        ,user_id        AS �Ұ��ֹ�
                        ,trim(pickuser.user_name)
                                        AS ��ȼ�
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,serial_no      AS Ϣ��
                    FROM
                        parts_pickup_linear
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_linear_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    �и˴�λ ����ɽ
    public function getViewDataEndList(&$result)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�         -- 00
                        ,parts_no       AS �����ֹ�         -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾           -- 02
                        ,plan           AS �ײ��           -- 03
                        ,user_id        AS �Ұ��ֹ�         -- 04
                        ,trim(pickuser.user_name)
                                        AS ��ȼ�           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������         -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����         -- 07
                        ,serial_no      AS Ϣ��             -- 08
                        ,pick_time      AS �и˹���         -- 09
                        -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                        ,CASE
                            WHEN CURRENT_DATE = CAST(end_time AS date)
                            THEN '���ͭ��'
                            ELSE '���̵��'
                        END             AS ���             -- 10
                    FROM
                        parts_pickup_linear
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_linear_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        end_time DESC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    �и���� ���ϸ�γ�ǧ�� ����ɽ
    public function getViewDataApendList($user_id, &$result)
    {
        // __construct �����ꤷ��$this->where ���Ȥ���� getViewDataList()�ȶ��Ѥ���
        $query = "SELECT plan_no        AS �ײ��ֹ�
                        ,parts_no       AS �����ֹ�
                        ,substr(midsc, 1, 20)
                                        AS ����̾
                        ,plan           AS �ײ��
                        ,user_id        AS �Ұ��ֹ�
                        ,trim(pickuser.user_name)
                                        AS ��ȼ�
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,serial_no      AS Ϣ��
                    FROM
                        parts_pickup_linear
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_linear_user AS pickuser USING(user_id)
                    {$this->where}
                    ORDER BY
                        str_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// Edit Confirm_delete 1�쥳����ʬ
    public function getViewDataEdit($serial_no, &$result)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�         -- 00
                        ,parts_no       AS �����ֹ�         -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾           -- 02
                        ,plan           AS �ײ��           -- 03
                        ,user_id        AS �Ұ��ֹ�         -- 04
                        ,trim(pickuser.user_name)
                                        AS ��ȼ�           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������         -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����         -- 07
                        ,serial_no      AS Ϣ��             -- 08
                        ,pick_time      AS Ϣ��             -- 09
                        --------------- ����ʲ��ϥꥹ�ȥǡ����ǤϤʤ�
                        ,to_char(str_time, 'YYYY') AS str_year   -- 10
                        ,to_char(str_time, 'MM')   AS str_month  -- 11
                        ,to_char(str_time, 'DD')   AS str_day    -- 12
                        ,to_char(str_time, 'HH24') AS str_hour   -- 13
                        ,to_char(str_time, 'MI')   AS str_minute -- 14
                        ,to_char(end_time, 'YYYY') AS end_year   -- 15
                        ,to_char(end_time, 'MM')   AS end_month  -- 16
                        ,to_char(end_time, 'DD')   AS end_day    -- 17
                        ,to_char(end_time, 'HH24') AS end_hour   -- 18
                        ,to_char(end_time, 'MI')   AS end_minute -- 19
                    FROM
                        parts_pickup_linear
                    LEFT OUTER JOIN
                        assembly_schedule using(plan_no)
                    LEFT OUTER JOIN
                        miitem on (parts_no=mipn)
                    LEFT OUTER JOIN
                        parts_pickup_linear_user AS pickuser USING(user_id)
                    WHERE
                        serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add('plan_no',    $res[0][0]);
            $result->add('assy_no',    $res[0][1]);
            $result->add('assy_name',  $res[0][2]);
            $result->add('plan_pcs',   $res[0][3]);
            $result->add('user_id',    $res[0][4]);
            $result->add('user_name',  $res[0][5]);
            $result->add('str_time',   $res[0][6]);
            $result->add('end_time',   $res[0][7]);
            $result->add('serial_no',  $res[0][8]);
            $result->add('pick_time',  $res[0][9]);
            // ������ʲ��Ͻ����ѥǡ���
            $result->add('str_year',   $res[0][10]);
            $result->add('str_month',  $res[0][11]);
            $result->add('str_day',    $res[0][12]);
            $result->add('str_hour',   $res[0][13]);
            $result->add('str_minute', $res[0][14]);
            $result->add('end_year',   $res[0][15]);
            $result->add('end_month',  $res[0][16]);
            $result->add('end_day',    $res[0][17]);
            $result->add('end_hour',   $res[0][18]);
            $result->add('end_minute', $res[0][19]);
        }
        return $rows;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �и� ��ȼ� ��Ͽ ����ɽ
    public function getViewUserList(&$result)
    {
        $query = "SELECT user_id        AS �Ұ��ֹ�
                        ,user_name      AS ��̾
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                        AS ��������
                        ,CASE
                            WHEN active THEN 'ͭ��'
                            ELSE '̵��'
                         END            AS ͭ��̵��
                    FROM
                        parts_pickup_linear_user
                    ORDER BY
                        user_id ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// List��    �и� ��ȼ� �ؼ��� �ܥ���ɽ��
    public function getViewActiveUser(&$result)
    {
        $query = "SELECT user_id        AS �Ұ��ֹ�
                        ,user_name      AS ��̾
                    FROM
                        parts_pickup_linear_user
                    WHERE
                        active
                    ORDER BY
                        user_id ASC
        ";
        $res = array();
        ///// �ڡ�������򤷤ʤ��᥽�åɤ���Ѥ���
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// �и� ��ꡦ��λ ���֤��ѹ� (�����������ڤӥ����å�)
    public function timeEdit($request)
    {
        // �Խ����¤Υ����å�
        if (!$this->pickupAuthUser()) return false;     // �Խ�����NG
        // �ꥯ�����ȥǡ��������
        $serial_no = $request->get('serial_no');
        $user_id   = $request->get('user_id');
        $str_year   = $request->get('str_year');
        $str_month  = $request->get('str_month');
        $str_day    = $request->get('str_day');
        $str_hour   = $request->get('str_hour');
        $str_minute = $request->get('str_minute');
        $end_year   = $request->get('end_year');
        $end_month  = $request->get('end_month');
        $end_day    = $request->get('end_day');
        $end_hour   = $request->get('end_hour');
        $end_minute = $request->get('end_minute');
        // ���դΥ����å�
        if ("{$str_year}{$str_month}{$str_day}" != "{$end_year}{$end_month}{$end_day}") {
            $_SESSION['s_sysmsg'] = '���ȴ�λ��ǯ������Ʊ���Ǥʤ��ƤϤʤ�ޤ���';
            return false;
        }
        // ���֤Υ����å�
        if ("{$str_hour}{$str_minute}" >= "{$end_hour}{$end_minute}") {
            $_SESSION['s_sysmsg'] = '���ȴ�λ�λ��֤�Ʊ������ž���Ƥ��ޤ���';
            return false;
        }
        // ����������
        $str_time = "{$str_year}-{$str_month}-{$str_day} {$str_hour}:{$str_minute}:00";
        $end_time = "{$end_year}-{$end_month}-{$end_day} {$end_hour}:{$end_minute}:00";
        // �ѹ��¹�
        return $this->timeEdit_execute($serial_no, $user_id, $str_time, $end_time);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ������ʽи˴ط����Խ����¥᥽�å�(���������ѥ᥽�åɰܹԤ���)
    protected function pickupAuthUser()
    {
        if ($this->getCheckAuthority($this->authDiv)) {
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '������ʽи˥�˥塼���Խ����¤�����ޤ���';
            return false;
        }
        
        ///// �ʲ��ϸ��߻��Ѥ��Ƥ��ʤ�
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {
            case '514':         // ���ץ���ʤ�OK
            case '534':         // ��˥����ʤ�OK
            case '500':         // �������ʤ�OK (2005/12/15�ɲ�)
            case '522':         // ���ץ���ΩMAô��
            case '523':         // ���ץ���ΩHAô��
            case '176':         // ���ץ���Ω ��Ĺ����̳
            case '551':         // ��˥���Ω��̳
            case '175':         // ��˥���Ωô��
            case '560':         // ��˥���Ω�Х����ô��
            case '537':         // ��˥���Ω����ô��
                return true;
                break;
            default:
                // NG
            }
            if ($_SESSION['Auth'] >= 3) { // �ƥ�����
                return true;
            }
        }
        $_SESSION['s_sysmsg'] = '������ʽи˥�˥塼���Խ����¤�����ޤ���';
        return false;
    }
    ////////// ������ʽи˻���(�٤߻��֤����)�ι��(ʬ)���֤�
    protected function getSumTime($str_time, $end_time)
    {
        // ��׻���(ʬ)�����(�٤߻��֤������)
        $query = "
            SELECT
            Uround(CAST(extract(epoch from timestamp '{$end_time}' - timestamp '{$str_time}') / 60 AS NUMERIC), 0)
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
    ////////// Ʊ���и����������ι�פ�Ʒ׻�����������
    protected function plan_pcsUpdate($user_id)
    {
        // Ʊ���и�ʬ�ι����������(plan_pcs)����� Ʊ���ȼԤξ���Ʊ���и�ʬ�ȸ��ʤ�
        $query = "
            SELECT sum(parts_pcs) FROM parts_pickup_linear
            WHERE end_time='19700101 000000' and user_id='{$user_id}'
        ";
        $plan_pcs = 0;     // �����
        $this->getUniResult($query, $plan_pcs);
        // ������Ʊ���и�ʬ��¾�ηײ褬�����plan_pcs��UPDATE����
        $query = "
            SELECT serial_no FROM parts_pickup_linear WHERE end_time='19700101 000000' and user_id='{$user_id}'
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1��Ǥ⤢��� UPDATE ����
            $query = "
                UPDATE parts_pickup_linear SET plan_pcs={$plan_pcs}
                WHERE end_time='19700101 000000' and user_id='{$user_id}'
            ";
            if (!$this->execute_Update($query)) {
                $_SESSION['s_sysmsg'] = 'Ʊ���и�ʬ�ι�������������ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return $plan_pcs;
    }
    ////////// ������ʽи˥�˥塼�κ�ȼԤ���Ͽ(�¹���)
    protected function user_insert($user_id, $user_name)
    {
        if (strlen($user_id) != 6) return false;
        
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO parts_pickup_linear_user
            (user_id, user_name, active, last_date, last_host)
            VALUES
            ('$user_id', '$user_name', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    ////////// ������ʽи˥�˥塼�κ�ȼԤ��ѹ�(�¹���)
    protected function user_update($user_id, $user_name)
    {
        if (strlen($user_id) != 6) return false;
        
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $update_sql = "
            UPDATE parts_pickup_linear_user SET
            user_id='{$user_id}', user_name='{$user_name}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE user_id='{$user_id}'
        "; 
        return $this->execute_Update($update_sql);
    }
    ////////// ������ʽи˥�˥塼�κ�ȼԤκ��(�¹���)
    protected function user_delete($user_id)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "SELECT * FROM parts_pickup_linear_user WHERE user_id='{$user_id}'";
        // �����SQLʸ������
        $delete_sql = "DELETE FROM parts_pickup_linear_user WHERE user_id='{$user_id}'";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// �и� ��ȼԤ�user_id��Ŭ��������å�����å������ܷ��(��̾=OK,false=NG)���֤�
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
    
    ////////// ����ܥ����ǡ����ι���
    protected function invoiceTOplan()
    {
        $file = '/home/guest/daily/W#TINZRH.TXT';
        if (!file_exists($file)) return;
        /////////// begin �ȥ�󥶥�����󳫻�
        if ($con = db_connect()) {
            query_affected_trans($con, 'BEGIN');
        } else {
            $_SESSION['s_sysmsg'] = "db_connect() error \n";
            return;
        }
        $fp = fopen($file, 'r');
        while (!(feof($fp))) {
            $data = fgetcsv($fp, 50, '_');     // �¥쥳���ɤ�38�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
            if (feof($fp)) {
                break;
            }
            if ($data[0] == '') continue;
            $num = count($data);       // �ե�����ɿ��μ���
            if ($num != 4) continue;
            for ($f=0; $f<$num; $f++) {
                $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                $data[$f] = addslashes($data[$f]);       // "'"�����ǡ����ˤ������\�ǥ��������פ���
            }
            $query_chk = "
                SELECT plan_no FROM invoice_to_plan WHERE invoice_no = '{$data[3]}' AND plan_no = '{$data[1]}'
            ";
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // �ȥ�󥶥��������Ǥ� �Ȳ����ѥ����꡼
                ///// ��Ͽ�ʤ� insert ����
                $query = "
                    INSERT INTO invoice_to_plan (kanri_no, plan_no, plan_pcs, invoice_no)
                    VALUES('{$data[0]}', '{$data[1]}', {$data[2]}, '{$data[3]}')
                ";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    $_SESSION['s_sysmsg'] = "db INSERT error \n";
                    return;
                }
            /*******************
            } else {
                ///// ��Ͽ���� update ����
                $query = "
                    UPDATE invoice_to_plan SET kanri_no='{$data[0]}', plan_no='{$data[1]}', plan_pcs={$data[2]}, invoice_no='{$data[3]}'
                    WHERE invoice_no = '{$data[3]}' AND plan_no = '{$data[1]}'
                ";
                if (query_affected_trans($con, $query) <= 0) {      // �����ѥ����꡼�μ¹�
                    $_SESSION['s_sysmsg'] = "db UPDATE error \n";
                    return;
                }
            *******************/
            }
        }
        fclose($fp);
        /////////// commit �ȥ�󥶥������λ
        query_affected_trans($con, 'COMMIT');
        unlink($file);
        return;
    }
    
    ////////// ����ܥ���No.�ˤ��Ϣ³�ײ輫ư���ϥ᥽�å�
    protected function invoicePlanAdd($invoice_no, $user_id)
    {
        $query = "
            SELECT plan_no FROM invoice_to_plan WHERE invoice_no = '{$invoice_no}'
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            for ($i=0; $i<$rows; $i++) {
                if (!$this->table_add_check($res[$i][0], $user_id)) {
                    return false;
                    // $_SESSION['s_sysmsg'] = '����ܥ���No.����μ�ư�ײ����Ϥ����ϤǤ��ʤ��ײ褬����ޤ�����';
                }
            }
            return true;
        }
        return false;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �и˳��Ϥ����� (�ɲ�)
    private function add_execute($plan_no, $user_id)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // Ʊ���и˷ײ�ν��ײ褫��
        $query = "
            SELECT str_time FROM parts_pickup_linear
            WHERE end_time='19700101 000000' and user_id='{$user_id}'
            LIMIT 1
        ";
        if ($this->getUniResult($query, $str_time) <= 0) {
            // ���ײ�Τ�����֤����ꤹ��
            $str_time = date('Y-m-d H:i:s');
        }
        
        // ���ηײ�(plan_no)����������(parts_pcs)�����
        $query = "
            SELECT count(parts_no) FROM allocated_parts LEFT OUTER JOIN miccc ON (parts_no=mipn)
            WHERE plan_no='{$plan_no}' and miccc IS NULL
        ";
        $parts_pcs = 0;     // �����
        $this->getUniResult($query, $parts_pcs);
        
        // ��Ͽ�¹� (���λ����Ǥ�plan_pcs��parts_pcs��Ʊ���ˤ���)
        $end_time = '19700101 000000';
        $insert_qry = "
            insert into parts_pickup_linear
            (plan_no, user_id, str_time, end_time, plan_pcs, parts_pcs, last_date, last_host)
            values
            ('$plan_no', '$user_id', '$str_time', '$end_time', $parts_pcs, $parts_pcs, '$last_date', '$last_host')
        ";
        $result_flg = $this->execute_Insert($insert_qry);
        
        // Ʊ���и�ʬ��plan_pcs�򹹿�
        if ($result_flg) {
            $this->plan_pcsUpdate($user_id);
        }
        return $result_flg;
    }
    
    ////////// �и˴�λ�����ϡ���� (�ѹ�)
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
                SELECT serial_no, plan_pcs, parts_pcs FROM parts_pickup_linear
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            // debug $_SESSION['sum_time'] = $sum_time;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                $plan_pcs  = $res[$i][1];
                $parts_pcs = $res[$i][2];
                // debug $_SESSION["pick_time$i"] = ($parts_pcs / $plan_pcs) * $sum_time;
                // debug $_SESSION["pick_round$i"] = round(($parts_pcs / $plan_pcs) * $sum_time, 0);
                $pick_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
                $update_sql = "
                    UPDATE parts_pickup_linear SET
                    end_time='{$end_time}', pick_time={$pick_time}, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                $this->execute_Update($update_sql);
            }
        } elseif ($status == 'cancel') {     // ��λ�μ��
            // serial_no�ǻ��ꤵ�줿 user_id��end_time ��Ʊ����λ�ײ�ʬ���������
            $query = "
                SELECT serial_no FROM parts_pickup_linear
                WHERE str_time='{$str_time}' AND user_id='{$user_id}'
            ";
            if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
            for ($i=0; $i<$rows; $i++) {
                $serial_no = $res[$i][0];
                // ��¸�Ѥ�SQLʸ������
                $save_sql = "SELECT * FROM parts_pickup_linear WHERE serial_no={$res[$i][0]}";
                $update_sql = "
                    UPDATE parts_pickup_linear SET
                    end_time='19700101 000000', pick_time=NULL, last_date='{$last_date}', last_host='{$last_host}'
                    WHERE serial_no={$serial_no}
                "; 
                // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
                $this->execute_Update($update_sql, $save_sql);
            }
        }
        return true;
    }
    
    ////////// �и����μ�� (�������)
    private function del_execute($serial_no, $user_id)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "select * from parts_pickup_linear where serial_no={$serial_no}";
        $delete_sql = "delete from parts_pickup_linear where serial_no={$serial_no}";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        $result_flg = $this->execute_Delete($delete_sql, $save_sql);
        
        // Ʊ���и�ʬ��plan_pcs�򹹿�
        if ($result_flg) {
            $this->plan_pcsUpdate($user_id);
        }
        return $result_flg;
    }
    
    ////////// �и� ��ꡦ��λ ���֤��ѹ�
    private function timeEdit_execute($serial_no, $user_id, $str_time, $end_time)
    {
        // �٤߻��֤��������׹���(ʬ)�����        
        $sum_time = $this->getSumTime($str_time, $end_time);
        // ��������֤����
        $query = "
            SELECT str_time FROM parts_pickup_linear
            WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $old_str_time) <= 0) {
            $_SESSION['s_sysmsg'] = '���ߤ������֤�����Ǥ��ޤ���';
            return false;
        }
        // ��ʬ��ޤ᤿Ʊ���ײ�ʬ�����
        $query = "
            SELECT serial_no, plan_pcs, parts_pcs FROM parts_pickup_linear
            WHERE str_time='{$old_str_time}' AND user_id='{$user_id}'
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] = '�оݥǡ���������ޤ���';
            return false;
        }
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        for ($i=0; $i<$rows; $i++) {
            $serial_no = $res[$i][0];
            $plan_pcs  = $res[$i][1];
            $parts_pcs = $res[$i][2];
            $pick_time = round(($parts_pcs / $plan_pcs) * $sum_time, 0);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            $update_sql = "
                UPDATE parts_pickup_linear SET
                str_time='{$str_time}', end_time='{$end_time}', pick_time={$pick_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            $this->execute_Update($update_sql);
        }
        return true;
    }
    
} // Class PartsPickupLinear_Model End

?>
