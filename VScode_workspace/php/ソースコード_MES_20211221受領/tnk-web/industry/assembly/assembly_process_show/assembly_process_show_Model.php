<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ���ӥǡ��� �Ȳ�         MVC Model ��                //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Model.php                     //
// 2006/01/20 _construct��$request->get('showGroup') != '0' && �ξ����ɲ� //
// 2006/04/12 ���ڡ�����ɽ���Կ��ν�������å���__construct()���ɲ�       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../ComTableMntClass.php');    // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
*         ��Ω ���� ��ꡦ���ӤξȲ��� MVC��Model���� base class ���쥯�饹�����      *
*****************************************************************************************/
class AssemblyProcessShow_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    
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
            if ($request->get('showGroup') != '0' && $request->get('showGroup') != '') {    // 0 �����Ƥ��̣����
                $where .= "AND group_no = " . $request->get('showGroup');
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
            break;
        case 'StartList':
        default:
            if ($request->get('showGroup') != '0' && $request->get('showGroup') != '') {    // 0 �����Ƥ��̣����
                $where = "WHERE end_time='19700101 000000' AND " . "group_no = " . $request->get('showGroup');
            } else {
                $where = "WHERE end_time='19700101 000000'";
            }
            $sql_sum = "
                SELECT count(*) FROM assembly_process_time $where
            ";
        }
        ///// SQLʸ��WHERE���Properties����Ͽ
        $this->where  = $where;
        ///// log file �λ���
        $log_file = 'assembly_process_show.log';
        ///// ���ڡ����ιԿ� ����� ����
        $query = "
            SELECT count(*) FROM assembly_process_group WHERE active
        ";
        $count = 0;
        $this->getUniResult($query, $count);
        $rows = ($count / 6);
        switch (TRUE) {
        case ($rows <= 1):
            $page = 20;
            break;
        case ($rows <= 2):
            $page = 18;
            break;
        case ($rows <= 3):
            $page = 16;
            break;
        default:
            $page = 14;
        }
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, $log_file, $page);
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
            $_SESSION['s_sysmsg'] = '��Ω ��� ���� ���� ��˥塼���Խ����¤�����ޤ���';
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ��Ω��� ����ɽ
    public function getViewStartList(&$result)
    {
        ///// �ʲ��� AS assyuser �� AS user �ǥ��顼�ˤʤ�(ͽ���)�����Ĺ������
        $query = "SELECT plan_no        AS �ײ��ֹ�         -- 00
                        ,parts_no       AS �����ֹ�         -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾           -- 02
                        ,plan_pcs       AS �ײ�Ŀ�         -- 03
                        ,user_id        AS �Ұ��ֹ�         -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�           -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������         -- 06
                        ,serial_no      AS Ϣ��             -- 07
                        ,plan           AS �ײ��           -- 08
                        -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                        ,serial_no      AS Ϣ��             -- 09
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ���Ͼܺ�         -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ��λ�ܺ�         -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS ����             -- 12
                        ,plan - cut_plan AS �ײ��          -- 13
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
    public function getViewEndList(&$result)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�     -- 00
                        ,parts_no       AS �����ֹ�     -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾       -- 02
                        ,plan_pcs       AS �ײ�Ŀ�     -- 03
                        ,user_id        AS �Ұ��ֹ�     -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�       -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������     -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����     -- 07
                        ,assy_time      AS ��׹���     -- 08
                        -----------------------------�ꥹ�ȤϾ嵭�ޤ�
                        ,serial_no      AS Ϣ��         -- 09
                        ,to_char(str_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ���Ͼܺ�     -- 10
                        ,to_char(end_time, 'YY/MM/DD HH24:MI:SS')
                                        AS ��λ�ܺ�     -- 11
                        ,CASE
                            WHEN plan_pcs > 0
                            THEN Uround(assy_time / plan_pcs, 3)
                            ELSE assy_time
                         END            AS ����         -- 12
                        ,plan - cut_plan AS �ײ��      -- 13
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
    
    ///// List��    ��Ω���롼��(��ȶ�) ��Ͽ���� ����ɽ (�ڡ�������ȥ���ʤ�)
    public function getViewGroupList(&$result)
    {
        $query = "SELECT group_no           AS ���롼���ֹ�         -- 00
                        ,group_name         AS ���롼��̾           -- 01
                        ------------------------ �ꥹ�ȤϾ嵭�ޤ�
                        ,CASE
                            WHEN div = 'C' THEN '���ץ�'
                            WHEN div = 'L' THEN '��˥�'
                            ELSE '̤��Ͽ'
                         END                AS ������               -- 02
                        ,CASE
                            WHEN product = 'C' THEN '���ץ�ɸ��'
                            WHEN product = 'S' THEN '���ץ�����'
                            WHEN product = 'L' THEN '��˥�����'
                            WHEN product = 'B' THEN '�Х����'
                            ELSE '̤��Ͽ'
                         END                AS ���ʥ��롼��         -- 03
                        ,to_char(last_date, 'YY/MM/DD HH24:MI')
                                            AS �ѹ�����             -- 04
                        ,CASE
                            WHEN active THEN 'ͭ��'
                            ELSE '̵��'
                         END                AS ͭ��̵��             -- 05
                        ,div                                        -- 06
                        ,product                                    -- 07
                    FROM
                        assembly_process_group
                    WHERE
                        active
                    ORDER BY
                        group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
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
    public function getViewDataEdit($serial_no, $request)
    {
        $query = "SELECT plan_no        AS �ײ��ֹ�             -- 00
                        ,parts_no       AS �����ֹ�             -- 01
                        ,substr(midsc, 1, 20)
                                        AS ����̾               -- 02
                        ,plan - cut_plan
                                        AS �ײ��               -- 03
                        ,user_id        AS �Ұ��ֹ�             -- 04
                        ,CASE
                            WHEN to_number(user_id, '999999') >= 777001 AND to_number(user_id, '999999') <= 777999
                            THEN '�����' || substr(user_id, 4, 3)
                            ELSE trim(assyuser.name)
                         END            AS ��ȼ�               -- 05
                        ,to_char(str_time, 'YY/MM/DD HH24:MI')
                                        AS ��������             -- 06
                        ,to_char(end_time, 'YY/MM/DD HH24:MI')
                                        AS ��λ����             -- 07
                        ,serial_no      AS Ϣ��                 -- 08
                        ,assy_time      AS ��׹���             -- 09
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
            $request->add('plan_no',    $res[0][0]);
            $request->add('assy_no',    $res[0][1]);
            $request->add('assy_name',  $res[0][2]);
            $request->add('plan',       $res[0][3]);
            $request->add('user_id',    $res[0][4]);
            $request->add('user_name',  $res[0][5]);
            $request->add('str_time',   $res[0][6]);
            $request->add('end_time',   $res[0][7]);
            $request->add('serial_no',  $res[0][8]);
            $request->add('assy_time',  $res[0][9]);
            // ������ʲ��Ͻ����ѥǡ���
            $request->add('str_year',   $res[0][10]);
            $request->add('str_month',  $res[0][11]);
            $request->add('str_day',    $res[0][12]);
            $request->add('str_hour',   $res[0][13]);
            $request->add('str_minute', $res[0][14]);
            $request->add('end_year',   $res[0][15]);
            $request->add('end_month',  $res[0][16]);
            $request->add('end_day',    $res[0][17]);
            $request->add('end_hour',   $res[0][18]);
            $request->add('end_minute', $res[0][19]);
        }
        return $rows;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ��Ω�ؼ���˥塼���Խ����¥����å��᥽�å�(���ѥ᥽�å�)
    protected function assemblyAuthUser()
    {
        $LoginUser = $_SESSION['User_ID'];
        $query = "select act_id from cd_table where uid='$LoginUser'";
        if (getUniResult($query, $sid) > 0) {
            switch ($sid) {             // �Ұ��ν�°�������祳���ɤǥ����å�
            case 500:                   // ������ (2005/12/15�ɲ�)
            case 176:
            case 522:
            case 523:
            case 525:
                return true;            // ���ץ���Ω(�������)
            case 551:
            case 175:
            case 560:
            case 537:
            case 534:
                return true;            // ��˥���Ω(��ࡦ���������)
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
        return number_format($res, 3);      // ����ε٤߻��֤ǰ����������˾����夬0�ξ��ʤ��ʤ뤿���ɲ�
    }
    ////////// Ʊ����� �ײ��ֹ�ηײ���ι�פ�Ʒ׻�����������
    protected function plan_pcsUpdate($request)
    {
        // Ʊ�� �ײ��ֹ�ι�׷ײ��(plan_pcs)����� Ʊ���ȼԤξ���Ʊ����Ω�ײ�ʬ�ȸ��ʤ�
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        $plan_all_pcs = 0;     // �����
        $this->getUniResult($query, $plan_all_pcs);
        // ������Ʊ����Ω���ʬ��¾�ηײ褬�����plan_all_pcs��UPDATE����
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1��Ǥ⤢��� UPDATE ����
            $update_sql = "
            UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            ";
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = 'Ʊ����Ω�ײ�ʬ�ι�׷ײ�����ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return $plan_all_pcs;
    }
    
    ////////// Ʊ����ȷײ��assy_time�ι��� (1��ȼԤ�Ʊ����� �ײ�)
    protected function assyTimeUpdate($request)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // �٤߻��֤��������׹���(ʬ)�����
        $sum_time = $this->getSumTime($request->get('str_time'), $request->get('end_time'));
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE
                (str_time='{$request->get('str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$request->get('end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            $update_sql = "
                UPDATE assembly_process_time SET
                assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = 'Ʊ����Ω�ײ�ʬ�ι������ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return true;
    }
    
    ////////// ��Ω ��ȼԤ�user_id��Ŭ��������å�����å������ܷ��(��̾=OK,false=NG)���֤�
    protected function checkUserID($user_id)
    {
        ///// user_id��Ŭ�������å�
        $chk = "SELECT trim(name) FROM user_detailes WHERE uid='{$user_id}'";
        if ($this->getUniResult($chk, $user_name) <= 0) {   // �Ұ���Ͽ����Ƥ��뤫
            if ($user_id < 777001 || $user_id > 777999) {   // �׻�(����)�Ǥʤ����
                $_SESSION['s_sysmsg'] = "�Ұ��ֹ桧{$user_id} ����Ͽ����Ƥ��ޤ���";
            } else {
                return ('�����' . substr($user_id, 3, 3) );
            }
        } else {
            return $user_name;
        }
        return false;
    }
    ////////// ���������ϴ�λ���֤���¸�Υǡ����Ƚ�ʣ���뤫�����å�(â��Ʊ�����ʬ�����)
    protected function DuplicateCheck($request, $str_time, $end_time)
    {
        // �����֤���ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (str_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(    -- ����Ʊ�����ʬ
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �����Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        // ��λ���֤���ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (end_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �δ�λ�Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        // ���ⴰλ���֤��ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time<'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time>'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (plan_no != '{$request->get('plan_no')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        return true;
    }
    
    ////////// ���������ϴ�λ���֤���¸�Υǡ����Ƚ�ʣ���뤫�����å�(â��Ʊ�����ʬ�ȼ�ʬ���Ȥ����)
    protected function DuplicateCheckEdit($request, $str_time, $end_time, $pre_str_time, $pre_end_time)
    {
        // �����֤���ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (str_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(    -- ����Ʊ�����ʬ
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �����Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        // ��λ���֤���ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (end_time>'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time<'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �δ�λ�Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        // ���ⴰλ���֤��ʣ������
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time<'{$str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            (end_time>'{$end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            AND
            NOT(
                (str_time='{$pre_str_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
                AND
                (end_time='{$pre_end_time}' AND group_no={$request->get('showGroup')} AND user_id='{$request->get('user_id')}')
            )
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "{$request->get('user_name')} ����� �ײ��ֹ桧{$duplicate} �Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        }
        return true;
    }
    
    ////////// Ʊ����Ȥηײ�ȷײ��ֹ椬��ʣ���뤫�����å�(��ʬ���Ȥ����)
    protected function DuplicatePlanNoCheck($request, $session)
    {
        $query = "
            SELECT plan_no FROM assembly_process_time
            WHERE
            (str_time>'{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            AND
            (end_time<'{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            AND
            serial_no != {$request->get('serial_no')}
            AND
            plan_no = '{$request->get('plan_no')}'
            limit 1
        ";
        if ($this->getUniResult($query, $duplicate) > 0) {
            $_SESSION['s_sysmsg'] = "Ʊ����ȷײ�ȷײ��ֹ桧{$request->get('plan_no')} ����ʣ���Ƥ��ޤ���";
            return false;
        }
        return true;
    }
    
    ////////// ��������ѹ�����Ʊ����� �ײ���ι�פ�Ʒ׻�����������
    protected function pre_plan_pcsUpdate($request, $session)
    {
        // Ʊ�� �ײ��ֹ�ι�׷ײ��(plan_pcs)����� Ʊ���ȼԤξ���Ʊ����Ω�ײ�ʬ�ȸ��ʤ�
        $query = "
            SELECT sum(plan_pcs) FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $plan_all_pcs = 0;     // �����
        $this->getUniResult($query, $plan_all_pcs);
        // ������Ʊ����Ω���ʬ��¾�ηײ褬�����plan_all_pcs��UPDATE����
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        if ($this->getUniResult($query, $tmp) > 0) {    // 1��Ǥ⤢��� UPDATE ����
            $update_sql = "
            UPDATE assembly_process_time SET plan_all_pcs={$plan_all_pcs}
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = 'Ʊ����Ω�ײ�ʬ�ι�׷ײ�����ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return $plan_all_pcs;
    }
    
    ////////// Ʊ����ȷײ��assy_time�ι��� (1��ȼԤ�Ʊ����� �ײ�)
    protected function pre_assyTimeUpdate($request, $session)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // �٤߻��֤��������׹���(ʬ)�����
        $sum_time = $this->getSumTime($session->get_local('pre_str_time'), $session->get_local('pre_end_time'));
        $query = "
            SELECT serial_no, plan_pcs, plan_all_pcs FROM assembly_process_time
            WHERE
                (str_time='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        for ($i=0; $i<$rows; $i++) {
            $serial_no    = $res[$i][0];
            $plan_pcs     = $res[$i][1];
            $plan_all_pcs = $res[$i][2];
            $assy_time = round(($plan_pcs / $plan_all_pcs) * $sum_time, 3);    // ���ײ�ʬ�ι���(ʬ)�򻻽�
            $update_sql = "
                UPDATE assembly_process_time SET
                assy_time={$assy_time}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE serial_no={$serial_no}
            "; 
            if (!$this->execute_Update($update_sql)) {
                $_SESSION['s_sysmsg'] = 'Ʊ����Ω�ײ�ʬ�ι������ѹ�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
            }
        }
        return true;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// ��Ω���Ӥ��ɲü¹�
    private function ApendExecute($request)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        $insert_sql = "
            INSERT INTO assembly_process_time
            (group_no, plan_no, user_id, str_time, end_time, plan_all_pcs, plan_pcs, assy_time, last_date, last_host)
            values
            ({$request->get('showGroup')}, '{$request->get('plan_no')}', '{$request->get('user_id')}', '{$request->get('str_time')}', '{$request->get('end_time')}'
            , {$request->get('plan')}, {$request->get('plan')}, {$request->get('assy_time')}, '{$last_date}', '{$last_host}')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ��Ω���Ӥκ���¹�
    private function DeleteExecute($request)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        
        // Ʊ����ȷײ�ʬ�Τ����ɬ�פʥǡ�������˻Ĥ�
        $query = "
            SELECT str_time, end_time, user_id FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $request->add('str_time', $res[0][0]);
            $request->add('end_time', $res[0][1]);
            $request->add('user_id',  $res[0][2]);
        } else {
            return false;
        }
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        $delete_sql = "
            DELETE FROM assembly_process_time
            WHERE serial_no={$request->get('serial_no')}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ��Ω���Ӥν����¹�
    private function EditExecute($request, $session)
    {
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $save_sql = "
            SELECT * FROM assembly_process_time WHERE serial_no={$request->get('serial_no')}
        ";
        // �ǽ��ñ�Ȥ��ѹ���¹�
        $update_sql = "
            UPDATE assembly_process_time SET
                plan_no='{$request->get('plan_no')}', user_id='{$request->get('user_id')}',
                str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                plan_all_pcs={$request->get('plan')}, plan_pcs={$request->get('plan')},
                assy_time={$request->get('assy_time')}, last_date='{$last_date}', last_host='{$last_host}'
            WHERE
                serial_no={$request->get('serial_no')}
        ";
        if (!$this->execute_Update($update_sql, $save_sql)) {
            return false;
        }
        // Ʊ����ȷײ褬¸�ߤ��뤫�����å�
        $query = "
            SELECT serial_no FROM assembly_process_time
            WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
        ";
        $rows = $this->getResult2($query, $res);
        // �Ұ��ֹ������å���Ʊ����ȷײ�ν�����ʬ��������
        if ($session->get_local('pre_user_id') == $request->get('user_id') && $rows > 0) {
            // �Ұ��ֹ椬Ʊ���ʤΤ�Ʊ����ȷײ��str_time��end_time���ѹ�
            $update_sql = "
                UPDATE assembly_process_time SET
                    str_time='{$request->get('str_time')}', end_time='{$request->get('end_time')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                WHERE
                (str_time<='{$session->get_local('pre_str_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
                AND
                (end_time>='{$session->get_local('pre_end_time')}' AND group_no={$request->get('showGroup')} AND user_id='{$session->get_local('pre_user_id')}')
            ";
            return $this->execute_Update($update_sql, $save_sql);
        } else {
            // �Ұ��ֹ椬�Ѥ�ä�����ñ�Ȥȸ��ʤ���Ʊ����ȷײ�������ѹ��Ϥ��ʤ�
            // ����Ʊ����ȷײ褬¸�ߤ��ʤ�(�ǽ�ϥȥ�󥶥������ǹԤäƤ�����Ʊ����ȷײ褬¸�ߤ��ʤ�����ñ�Ȥι���������ʤ��ʤ뤿����̤ˤ���)
        }
        return true;
    }
    
} // Class AssemblyProcessShow_Model End

?>
