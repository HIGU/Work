<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                                              MVC Model ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_Model.php                                    //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class

require_once ('../equip_function.php');        // ������˥塼 ���� function (function.php��ޤ�)

/******************************************************************************
*          ����ϡʿ������� MVC��Model�� base class ���쥯�饹�����          *
******************************************************************************/
class Monitoring_Model extends ComTableMnt
{
    ///// Private properties
    private $plan_no;
    private $parts_no   = '--------';
    private $parts_name = '- - - - - - - -';
    private $plan       = '-,---,---';
    private $dead_lines = '';
    private $run_state  = '[���]';
    private $run_time   = '--��--��-- ';
    private $state      = '';
    private $header_info= 'none';
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        $this->plan_no = $request->get('plan_no');

        if( $this->plan_no == '--------' ) {
            $this->plan_no = '';
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            ;
        }

//        $sql_sum = "SELECT count(*) FROM user_detailes where uid like '%{$syainbangou}'";

        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
//        parent::__construct($sql_sum, $request, 'monitoring.log');
    }

    // �ײ��ֹ�Ϥ���ޤ�����
    public function IsPlanNo()
    {
        if( $this->plan_no == '' ) {
            return false;
        } else {
            return true;
        }
    }

    // ���åȤ���Ƥ���ײ��ֹ���֤���
    public function GetPlanNo()
    {
        return $this->plan_no;
    }

    // ���åȤ���Ƥ��������ֹ���֤���
    public function GetPartsNo()
    {
        return $this->parts_no;
    }

    // ���åȤ���Ƥ�������̾���֤���
    public function GetPartsName()
    {
        return $this->parts_name;
    }

    // ���åȤ���Ƥ���ײ�����֤���
    public function GetPlan()
    {
        return $this->plan;
    }

    // ���åȤ���Ƥ��봰λ����Ǽ�����Ѵ����֤���
    public function GetDeadLines()
    {
//        return $this->dead_lines;
        return ltrim(substr($this->dead_lines,4,2),0) . "�� " . ltrim(substr($this->dead_lines,6,2),0) . "��";
    }

    // ���åȤ���Ƥ������������֤���
    public function GetProNum($plan_no,$mac_no)
    {
        $query = "
                    SELECT  work_cnt
                    FROM    equip_work_log2_moni
                    WHERE   plan_no='{$plan_no}' and mac_no={$mac_no}
                    ORDER BY date_time DESC LIMIT 1
                 ";
        $res = array();
        if ( ($rows=getResult($query, $res)) < 0) {
            return 0;
        } else if( $rows == 0 ) {
            return 0;
        }
        $pro_num = $res[0][0];
        return $pro_num;
    }

    // ���åȤ���Ƥ����Ư�������֤���
    public function GetRunState($plan_no,$mac_no, &$bg_color, &$txt_color)
    {
        $koutei=1;
        $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                ORDER BY date_time DESC LIMIT 1
        ";
        /*
        $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                limit 1
        ";
        */
        $res = array();
        if ( ($rows=getResult2($query, $res)) <= 0) {
            $mac_state_txt = '---';
        } else {
            $mac_state_txt = equip_machine_state($mac_no, $res[0][2], $bg_color, $txt_color);
        }
        return $mac_state_txt;
    }

    // ���åȤ���Ƥ����Ư���֤��֤���
    public function GetRunTime($plan_no,$mac_no)
    {
        $koutei=1;
        $query = "select date_time
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time DESC
                limit 1
        ";
        /*
        $query = "select date_time
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                limit 1
        ";
        */
        $res = array();
        if ( ($rows=getResult2($query, $res)) <= 0) {
            $run_time = '--��--��-- ';
        } else {
            $query2 = "select date_time
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
                limit 1
            ";
            /*
            $query2 = "select date_time
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                limit 1
            ";
            */
            $res2 = array();
            if ( ($rows2=getResult2($query2, $res2)) <= 0) {
                $run_time = '--��--��-- ';
            } else {
                $timestamp  = strtotime($res[0][0]);
                $timestamp2 = strtotime($res2[0][0]);
                $time_temp  = $timestamp - $timestamp2;
                $hours      = floor($time_temp / 3600);
                $minutes    = floor(($time_temp / 60) % 60);
                $seconds    = $time_temp % 60;
//                $run_time = $hours . ':' . $minutes . ':' . $seconds;
                $run_time = sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
            }
        }
        return $run_time;
    }

    // ɽ�����륭��ץ������֤���
    public function GetCaption($mode='')
    {
        if( $mode == 'select' ) {
            return "���ˡ���ž�򳫻Ϥ��뵡�������򤷤Ʋ�������";
        }
        
        if( $this->IsPlanNo() ) {
            return "�� ɽ�����Ƥ��ǧ���Ʋ�������";
        } else {
            return "�ڷײ��ֹ�ۤ����Ϥ��Ʋ�������";
        }
    }

    // ����å����줿�ܥ��������֤���(start/reset/break/restart/delete/end)
    public function GetState()
    {
        return $this->state;
    }

    // ���åȤ���Ƥ���إå���������֤���(none/run/break/end)
    public function GetHeaderInfo()
    {
        return $this->header_info;
    }

    // ����($m_no)�ǵ���̾���֤�
    public function GetMacName($m_no)
    {
        $query = "
                    SELECT  mac_name     AS ����̾ -- 00
                    FROM    equip_machine_master2
                    WHERE   mac_no='{$m_no}'
                 ";
        $res = array();
        if ( getUniResult($query, $res) <= 0) {
            return '--------';
        }
        return $res;
    }

    // ����($factory)�ε����ޥ�����������֤�
    function GetFactoryMachineInfo(&$res, $factory)
    {
        $query = "
                    SELECT   mac_no     AS �����ֹ� -- 00
                            ,mac_name   AS ����̾   -- 01
                            ,survey     AS �ƻ�     -- 02
                    FROM    equip_machine_master2
                    WHERE   factory={$factory}
                    ORDER BY mac_no
                 ";
        $res = array();
        if ( ($rows=getResult($query, $res)) < 0) {
            $_SESSION['s_sysmsg'] = '�����ޥ������μ����˼��ԡ�(equip_machine_master2)';
        } else if( $rows == 0 ) {
            echo "�ޤ���<font style='color:Red;'>�ڵ����ޥ��������ݼ��</font>��굡������Ͽ���Ʋ�������";
        }
        return $rows;
    }

    // ����($m_no)�ǲ�Ư��ηײ��ֹ���֤�
    public function GetRunningPlanNo($m_no)
    {
        $query = "
                    SELECT  plan_no     AS �ײ��ֹ� -- 00
                    FROM    equip_work_log2_header_moni
                    WHERE   mac_no='{$m_no}' AND work_flg='t'
                 ";
        $res = array();
        if ( getUniResult($query, $res) <= 0) {
            return '--------';
        }
        return $res;
    }

    // ɽ��
    public function GetViewDate($request)
    {
        if( $this->SetDispPlanData() ) {    // �ײ��ֹ�ξ������
            $m_no = $request->get('m_no');
            $plan_no = $this->GetPlanNo();
            $parts_no = $this->GetPartsNo();
            $koutei_no = 1; // �Ȥꤢ������1 ����롣
            $plan = $this->GetPlan();
            $jisseki = $plan; // �ײ��������롣
        } else {
            return; // ���ꤵ�줿�ײ��ֹ�ξ�������Ǥ��ʤ��ä���
        }

        $this->state = $request->get('state');

        switch( $this->state ) {
            case 'delete':         // ���
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ���� ������ ��������1�ˤϲ���';
                $this->HeaderInfoDel($m_no, $plan_no, $koutei_no);
                $this->plan_no = '';
                break;
            case 'reset':       // �ꥻ�å�
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ��ꥻ�åȢ��ײ��ֹ�����ɹ��ߤ����';
                $this->plan_no = '';
                break;
            case 'end':         // ��λ
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ���λ ������ ��������1��/���ӡ�10�ˤϲ���';
                $this->HeaderInfoEnd($m_no, $plan_no, $parts_no, $koutei_no, $jisseki);
                $this->state = 'start';
//                $this->plan_no = '';
                break;
            case 'break':       // ����
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ����� ���� ͽ���';
                $this->HeaderInfoBreak($m_no, $plan_no, $koutei_no, false);
                $this->state = 'start';
                $this->header_info = 'break';
//                $this->plan_no = '';
                break;
            case 'restart':     // �Ƴ�
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ��Ƴ� ���� ͽ�� ����';
                $query = "select mac_no from equip_work_log2_header_moni where mac_no='$m_no' and work_flg is TRUE and end_timestamp is NULL";
                $res = array();
                if(($rows=getResult($query, $res)) >= 1) {    // �إå����˴��ˤʤ��������å�
                    $_SESSION['s_sysmsg'] = "<font color='yellow'>�����ֹ� = $m_no �ϡ����߲�ư��Ǥ�!!</font>";
                } else {
                    $this->HeaderInfoBreak($m_no, $plan_no, $koutei_no, true);
                }
            case 'plan_load':   // �ɹ���
            case 'start':       // ����
                $this->header_info = $this->LoadHeaderInfo($m_no, $plan_no, $koutei_no);
                if( $this->header_info == 'none' ) {
                    if( $this->state == 'plan_load' ) {
//                        $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ��ɹ��ߡ�';
                        break;
                    }
                    if( $this->HeaderInfoAdd($m_no, $plan_no, $parts_no, $koutei_no, $plan) ) {
//                        $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ���ž���ϡ���';
                        $this->header_info = 'run';
                    }else{
                        $_SESSION['s_sysmsg'] .= '��ž���Ͼ���κ����˼���(equip_work_log2_header_moni)';
                    }
                    break;
                } else if( $this->header_info == 'run' ) {
//                    $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ���Ư���';
                } else if( $this->header_info == 'break' ) {
                    $_SESSION['s_sysmsg'] .= '���Ƿײ�ˤ���ޤ���';
                } else if( $this->header_info == 'end' ) {
                    $_SESSION['s_sysmsg'] .= '���ˡ���λ����Ƥ��ޤ���';
                }
                $this->state = 'start';
                break;
            default:    // ��Ư��ξ��
//                $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ���Ư�� ���� ������';
                $this->header_info = $this->LoadHeaderInfo($m_no, $plan_no, $koutei_no);
                $this->state = 'start';
                break;
        }
    }

    // ɽ������ײ��ֹ�ξ���򥻥å�
    public function SetDispPlanData()
    {
        // �ײ��ֹ椫�����ʾ���μ���
        $query = "
                    SELECT          parts_no                AS �����ֹ�     -- 00
                                    ,substr(midsc, 1, 20)   AS ����̾       -- 01
                                    ,plan-cut_plan          AS �ײ��       -- 02
                                    ,kanryou                AS ��λ��       -- 03
                    FROM            assembly_schedule
                    LEFT OUTER JOIN miitem ON (parts_no=mipn)
                    WHERE plan_no='{$this->GetPlanNo()}'
                 ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = '�ײ��ֹ��'.$this->GetPlanNo().'�ۤ����ʾ��������Ǥ��ޤ���';
            $this->plan_no      = '';
            return false;
        } else {
            $this->parts_no     = $res[0][0];
            $this->parts_name   = $res[0][1];
            $this->plan         = $res[0][2];
            $this->dead_lines   = $res[0][3];   // Ǽ��
            return true;
        }
    }

    // DB���إå���������ɤ߹��ߥ��ơ��������֤���(none/run/break/end)
    public function LoadHeaderInfo($m_no, $plan_no, $koutei_no)
    {
        $query = "
                    SELECT  end_timestamp, work_flg
                    FROM    equip_work_log2_header_moni
                    WHERE   mac_no=$m_no AND plan_no='$plan_no' AND koutei=$koutei_no
                 ";
        $res = array();
        if( getResult($query, $res) <= 0 ) {
            return 'none';  // �ޤ�¸�ߤ��ʤ���
        }
        if( $res[0][0] == '' && $res[0][1] == 't' ) {
            return 'run';   // ��Ư��
        } else if( $res[0][0] == '' && $res[0][1] == 'f' ) {
            return 'break'; // ������
        } else {
            return 'end';   // ��λ
        }
    }

    // ��ž���Ͼ�������
    public function HeaderInfoAdd($m_no, $plan_no, $parts_no, $koutei_no, $plan)
    {
        $str_timestamp = date('Y-m-d H:i:s');
        $insert_qry = "insert into equip_work_log2_header_moni (mac_no, plan_no, parts_no, koutei, plan_cnt, str_timestamp, work_flg) 
                values($m_no, '$plan_no', '$parts_no', $koutei_no, $plan, '$str_timestamp', TRUE)";
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($insert_qry)>=0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg  = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg  = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
        return false;
    }

    // ��¤�ݤε�����ž ��λ�ؼ� equip_header ��end_timestamp�˴�λ���ֽ���� work_flg �� FALSE ��
    public function HeaderInfoEnd($m_no, $plan_no, $parts_no, $koutei_no, $jisseki)
    {
        $query = "select mac_no,plan_no,parts_no,koutei from equip_work_log2_header_moni where work_flg=TRUE 
                and mac_no='$m_no' and plan_no='$plan_no' and parts_no='$parts_no' and koutei='$koutei_no'";
        $res = array();
        if( getResult($query, $res) >= 1) {         // �ǡ����١����Υإå�����걿ž��Υǡ���������å�
            ; // OK
        } else {
            $_SESSION['s_sysmsg'] = "�����ֹ�:$m_no �ײ��ֹ�:$plan_no �����ֹ�:$parts_no ����:$koutei_no �Ǥ���Ͽ����Ƥ��ޤ���";
            return; // NG
        }

        $end_timestamp = date('Y-m-d H:i:s');
        $update_qry = "update equip_work_log2_header_moni set end_timestamp='$end_timestamp', work_flg=FALSE, jisseki={$jisseki}
                       where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}"; 
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($update_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery:��λ:$update_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect:��λ:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    }

    // ��¤�ݤε�����ž ����/�Ƴ����� �إå����ե�������� work_flg IS FALSE(����) TRUE(�Ƴ�)
    public function HeaderInfoBreak($m_no, $plan_no, $koutei_no, $flag)
    {
        if( $flag ) {
//          $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ�** �Ƴ� **��';
            $update_qry = "update equip_work_log2_header_moni set work_flg=TRUE
                           where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}";
        } else {
//          $_SESSION['s_sysmsg'] .= '�ڥƥ��ȣͣӣǡ�** ���� **��';
            ///// �����ޥ������� csv_flg ���� Netmoni/�����꡼�����å������μ���
            $query = "select mac_name, csv_flg from equip_machine_master2 where mac_no={$m_no} limit 1";
            $res = array();
            if (($rows=getResult($query,$res))>=1) {      // �����ޥ��������鵡��̾���������
                $name = substr($res[0][0],0,10);
                $csv_flg = $res[0][1];
            } else {
                $name = "     ";
                $csv_flg = 0;       // 1�ʳ��ϥ����꡼�����å������Ȥ���
            }
            
            // equip_work_log �����ǥǡ�����񤭹��ि��ǿ��ǡ������ǧ����
                // ��SQL = where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no} and mac_state<>0 order by date_time DESC limit 1";
            $query = "select work_cnt from equip_work_log2_moni
                WHERE
                plan_no='{$plan_no}' and mac_no={$m_no} and koutei={$koutei_no}
                AND
                mac_state != 0
                ORDER BY date_time DESC
                LIMIT 1
            ";
            /*
            $query = "select work_cnt from equip_work_log2_moni
                WHERE
                equip_moni_index(mac_no, plan_no, koutei, date_time) > '{$m_no}{$plan_no}{$koutei_no}00000000000000'
                AND
                equip_moni_index(mac_no, plan_no, koutei, date_time) < '{$m_no}{$plan_no}{$koutei_no}99999999999999'
                AND
                mac_state != 0
                ORDER BY equip_moni_index(mac_no, plan_no, koutei, date_time) DESC
                LIMIT 1
            ";
            */
            $res=array();
            if (($rows=getResult($query,$res))>=1) {      // �ǿ��ǡ�������������Υǡ����򥻥åȤ���
                $pre_cnt  = $res[0][0];
                if ($csv_flg == 1) {    // Netmoni���� = 15(����)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, $pre_cnt, '{$plan_no}', {$koutei_no})
                            ";
                } else {                // �����꡼�����å����� = 9(����)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, $pre_cnt, '{$plan_no}', {$koutei_no})
                            ";
                }
            } else {
                if ($csv_flg == 1) {    // Netmoni���� = 15(����)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                    values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, 0, '{$plan_no}', {$koutei_no})
                                ";
                } else {                // �����꡼�����å����� = 9(����)
                    $insert_qry = "insert into equip_work_log2_moni (mac_no, date_time, mac_state, work_cnt, plan_no, koutei)
                                    values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, 0, '{$plan_no}', {$koutei_no})
                                ";
                }
            }
            if (funcConnect()) {
                execQuery('begin');
                if (execQuery($insert_qry) >= 0) {
                    execQuery('commit');
                    disConnectDB();
                } else {
                    execQuery("rollback");
                    disConnectDB();
                    $error_msg = date('Y/m/d H:i:s', mktime());
                    $error_msg .= "-execQuery: $insert_qry";
                    `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
                }
            } else {
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-funcConnect: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }

            $update_qry = "update equip_work_log2_header_moni set work_flg=FALSE
                           where mac_no={$m_no} and plan_no='{$plan_no}' and koutei={$koutei_no}"; 
        }
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($update_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery:��λ:$update_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect:��λ:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    }

    // ��¤�ݤε�����ž���ϥǡ������(�إå����ե����� & ����)(�ȥ�󥶥���������)
    public function HeaderInfoDel($m_no, $plan_no, $koutei_no)
    {
        $delete_header = "delete from equip_work_log2_header_moni where mac_no={$m_no} and plan_no='{$plan_no}' and 
                            koutei={$koutei_no}";
        $delete_work = "
            DELETE FROM equip_work_log2_moni
            WHERE
            plan_no='{$plan_no}' and mac_no={$m_no} and koutei={$koutei_no}
        ";
        /*
        $delete_work = "
            DELETE FROM equip_work_log2_moni
            WHERE
            equip_moni_index(mac_no, plan_no, koutei, date_time) > '{$m_no}{$plan_no}{$koutei_no}00000000000000'
            AND
            equip_moni_index(mac_no, plan_no, koutei, date_time) < '{$m_no}{$plan_no}{$koutei_no}99999999999999'
        ";
        */
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($delete_header)>=0) {
//                execQuery('commit');
//                disConnectDB();
//                return true;
                if (execQuery($delete_work)>=0) {
                    execQuery('commit');
                    disConnectDB();
                    return true;
                } else {
                    execQuery('rollback');
                    disConnectDB();
                    $error_msg = date('Y/m/d H:i:s', mktime());
                    $error_msg .= "-execQuery: $delete_work Transaction";
                    `echo "$error_msg" >> /tmp/equipment_write_error.log`;
                }
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $delete_header Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $delete_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
        return false;
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class Monitoring_Model End
?>
