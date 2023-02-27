<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                                              MVC Model ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Model.php                                        //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class

/******************************************************************************
*          ����ϡʿ������� MVC��Model�� base class ���쥯�饹�����          *
******************************************************************************/
class Sougou_Model extends ComTableMnt
{
    ///// Private properties
    private $syain;
    private $syainbangou;
    private $mail;
    private $approval;
    private $approvalpath;
    private $indx;
    private $rows;
    private $res;
    private $yukyu = array(array(0,0,0,0,0));
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $syainbangou='')
    {
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        $this->syainbangou = $request->get("syain_no");

        if ($syainbangou == '') {
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            $syainbangou = sprintf('%06s', $syainbangou);
            if( $syainbangou == 0 ) return;
            $this->syainbangou = $syainbangou;    // Properties�ؤ���Ͽ
            $request->add('syainbangou', $syainbangou);
            $this->getAMandTimeVacationData();
        }
        $sql_sum = "
            SELECT count(*) FROM user_detailes where uid like '%{$syainbangou}'
        ";

        // �࿦���Ƥʤ��������å�
        $query = "
            SELECT      uid
            FROM        user_detailes
            WHERE       uid = '$syainbangou' AND retire_date IS NULL
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $this->syain = false;
        } else {
            $this->syain = true;
            if( $this->setYukyu() ) {
//if($syainbangou == '300667' ) {   // �������ǤϤޤ������������åȤ���Ƥʤ�
//                $this->setYotei($request->get("sin_date"));
//}
            }
        }

        $this->approval = $this->getApprovalPath($request, $syainbangou);

        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'sougou.log');
    }

    // ����Ϥ����Ǥ���Ұ�
    public function IsInputPossible()
    {
        if( !$this->IsSyain() ) {
            return false;
        }
        if( !$this->IsApproval() ) {
            return false;
        }
        return true;
    }

    // TNK�μҰ�
    public function IsSyain()
    {
        return $this->syain;
    }

    // ��ǧ�롼����Ͽ����Ƥ���
    public function IsApproval()
    {
        return $this->approval;
    }

    // ��ǧ�롼��
    public function getApproval()
    {
        return $this->approvalpath;
    }

    // DB���
    public function getIndx()
    {
        return $this->indx;
    }

    // DB�Կ�
    public function getRows()
    {
        return $this->rows;
    }

    // DB
    public function getRes()
    {
        return $this->res;
    }

    // �����Ǥ�����
    public function IsHoliday($date)
    {
//if( $this->syainbangou == '300667' ) $_SESSION['s_sysmsg'] .= $date . ' : �Ǥ�';
        if( !$date ) return false;

        $query = "
                SELECT  tdate           AS ����,     -- 0
                        bd_flg          AS �Ķ���,   -- 1
                        note            AS ������  -- 2
                FROM    company_calendar
                WHERE   tdate = '{$date}' AND bd_flg = 'f'
            ";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
//        $_SESSION['s_sysmsg'] .= $date . ' : �����Ǥ�';

        return true;
    }

    // ������֤ε����������
    public function getHolidayRang($s_year, $e_year)
    {
        if( !$s_year || !$e_year ) return "";

        $query = "
                SELECT  tdate           AS ����
                FROM    company_calendar
                WHERE   tdate >= '{$s_year}0101' AND tdate <= '{$e_year}1231' AND bd_flg = 'f'
            ";
        if( getResult2($query, $res) <= 0 ) {
            return "";
        }
        return $res;
    }

    // ͭ�ټ������ʼ��ӡ�
    public function KeikakuCnt()
    {
        $this->yukyu[0][0]; // ����ͭ������
        $this->yukyu[0][1]; // ����ͭ�ٻ�
        $this->yukyu[0][2]; // Ⱦ��ͭ�ٲ��
        $this->yukyu[0][3]; // ���ֵټ���ʬ
        $this->yukyu[0][4]; // ����ͭ�ٸ���
        
        if( $this->yukyu[0][3] == 0 ) {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] );
        }else {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] + (round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3)) );
        }

        return $jisseki;
    }

    // ���̷ײ�ͭ�ٿ������
    public function GetSpecialPlans($sin_date)
    {
        $s_day = substr($sin_date,0,4)-1 . "-03-31";
        $query = "
                SELECT  start_date      AS ������,      -- 0
                        end_date        AS ��λ��,      -- 1
                        start_time      AS ���ϻ���,    -- 2
                        end_time        AS ��λ����,    -- 3
                        content         AS ����         -- 4
                FROM    sougou_deteils
                WHERE   (start_date<'{$sin_date}' OR end_date<'{$sin_date}')
                    AND (start_date>'{$s_day}' OR end_date>'{$s_day}')
                    AND uid='{$this->syainbangou}'
                    AND yukyu='���̷ײ�'
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : ͽ��ʤ�';
            return 0;
        }
        // ���̷ײ�ͭ�ٿ��η׻�����
        $cnt = 0;   // �����󥿡�
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($s_day) ) {  // �������ȴ��ΤϤ�������
                // ���ΤϤ�������ξ�硣�������˴��ΤϤ������򥻥å�
                $res[$i][0] = date('Ymd', strtotime($s_day . '1 day'));
            }
            if( strtotime($res[$i][1]) > strtotime($sin_date) ) {  // ��λ���ȿ����������
                // �������ʹߤξ�硣��λ���˿������������򥻥å�
                $res[$i][1] = date('Ymd', strtotime($sin_date . '- 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // �Ķ�����μ�������
            if( trim($res[$i][4]) == 'ͭ��ٲ�' ) {
                $cnt += $day;
            } else if( trim($res[$i][4]) == 'AMȾ��ͭ��ٲ�' || trim($res[$i][4]) == 'PMȾ��ͭ��ٲ�' ) {
                $cnt += (0.5 * $day);
            } else if( trim($res[$i][4]) == '����ñ��ͭ��ٲ�' ) {
                $houer = ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // ���ֵٷ׻�;
                $cnt += round($houer/($this->yukyu[0][4]/5), 3);
            }
        }
        return $cnt;
    }

    // ͽ��ͭ�١ʿ����ѡ�
    public function YoteiKyuka($sin_date, $half)
    {
        if( $sin_date == "" ) return;

        $cnt = 0;
        if( $half ) {
            $s_day = substr($sin_date,0,4) . "-03-31";
            $e_day = substr($sin_date,0,4) . "-10-01";
        } else {
            $s_day = substr($sin_date,0,4)   . "-09-30";
            $e_day = substr($sin_date,0,4)+1 . "-04-01";
        }
        // ͽ��ͭ�پ������
        $query = "
                    SELECT  start_date      AS ������,      -- 0
                            end_date        AS ��λ��,      -- 1
                            content         AS ����         -- 2
                    FROM    sougou_deteils
                    WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                        AND start_date>'{$s_day}' AND end_date<'{$e_day}'
                        AND uid='{$this->syainbangou}'
                        AND (content='ͭ��ٲ�' OR content='AMȾ��ͭ��ٲ�' OR content='PMȾ��ͭ��ٲ�' )
                        AND yukyu!='���̷ײ�'
                        AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
                 ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : ͽ��ʤ�';
            return 0;
        }

        // ͭ�ٻĤ�ͽ��ͭ�٤βû�����
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // �������ȿ����������
                // �������ȿ���������Ӥ��������������ξ�硣�������˿������μ������򥻥å�
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // �Ķ�����μ�������
            if( trim($res[$i][2]) == 'ͭ��ٲ�' ) {
                $cnt += $day;
            } else if( trim($res[$i][2]) == 'AMȾ��ͭ��ٲ�' || trim($res[$i][2]) == 'PMȾ��ͭ��ٲ�' ) {
                $cnt += (0.5 * $day);
            }
        }
        return $cnt;
    }

    // �������
    public function getKi()
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // ���׻�����195603
        $tmp = $tmp / 100;             // ǯ����ʬ����Ф�
        $ki  = ceil($tmp);             // roundup ��Ʊ��

        return $ki;
    }

    // ���ꤷ������ͭ�ٻľ���ե����뤬¸�ߤ��뤫��
    public function IsKiInfoFile($ki)
    {
        $query = "
                    SELECT  *
                    FROM    holiday_rest_master
                    WHERE   uid='{$this->syainbangou}' and ki={$ki}
                    ORDER   BY ki DESC LIMIT 1
                 ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            return false;
        }

        return true;
    }

    // ͭ�ٻľ�������
    public function getYukyu()
    {
        return $this->yukyu;
    }

    // ͭ�ٻľ���׻��������Ұ���˥塼 view_mineinfo.php �ե������������
    public function setYukyu()
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // ���׻�����195603
        $tmp = $tmp / 100;             // ǯ����ʬ����Ф�
        $ki  = ceil($tmp);             // roundup ��Ʊ��
        $query = "
                SELECT
                     current_day    AS ����ͭ������     -- 0
                    ,holiday_rest   AS ����ͭ�ٻ�       -- 1
                    ,half_holiday   AS Ⱦ��ͭ�ٲ��     -- 2
                    ,time_holiday   AS ���ֵټ���ʬ     -- 3
                    ,time_limit     AS ����ͭ�ٸ���     -- 4
                    ,web_ymd        AS ����ǯ����       -- 5
                FROM holiday_rest_master
                WHERE uid='{$this->syainbangou}' and ki<={$ki}
                ORDER BY ki DESC LIMIT 1
            ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            $this->yukyu = array(array(0,0,0,0,0));
            return false;
        }
        return true;
    }

    // ͭ�ٻĤ�ͽ��ͭ�ٲû�
    public function setYotei($sin_date)
    {
        if( $sin_date == "" ) return;

        // ͽ��ͭ�پ������
        $query = "
                SELECT  start_date      AS ������,      -- 0
                        end_date        AS ��λ��,      -- 1
                        start_time      AS ���ϻ���,    -- 2
                        end_time        AS ��λ����,    -- 3
                        content         AS ����         -- 4
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND uid='{$this->syainbangou}'
                    AND (content='ͭ��ٲ�' OR content='AMȾ��ͭ��ٲ�' OR content='PMȾ��ͭ��ٲ�' OR content='����ñ��ͭ��ٲ�' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : ͽ��ʤ�';
            return;
        }

        // ͭ�ٻĤ�ͽ��ͭ�٤βû�����
        $timecount = false;     // ���ֵ٥ե饰�����
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // �������ȿ����������
                // �������ȿ���������Ӥ��������������ξ�硣�������˿������μ������򥻥å�
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // �Ķ�����μ�������
            if( trim($res[$i][4]) == 'ͭ��ٲ�' ) {
                $this->yukyu[0][1] -= $day;         // ����ͭ�ٻ�
            } else if( trim($res[$i][4]) == 'AMȾ��ͭ��ٲ�' || trim($res[$i][4]) == 'PMȾ��ͭ��ٲ�' ) {
                $this->yukyu[0][1] -= (0.5 * $day); // ����ͭ�ٻ�
                $this->yukyu[0][2] += $day;         // Ⱦ��ͭ�ٲ��
            } else if( trim($res[$i][4]) == '����ñ��ͭ��ٲ�' ) {
                if( !$timecount ) {
                    // ����ͭ�ٻĤش��˼������Ƥ�����ֵ٤�û����롣
                    // �����Τޤ�ͭ�ٻĤ�긺������Ȥ��������ͤˤʤäƤ��ޤ����ᡣ
                    $this->yukyu[0][1] += round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
                    $timecount = true;  // ���ֵ٥ե饰���å�
                }
                $this->yukyu[0][3] += ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // ���ֵٲû�
            }
        }
        if( $timecount ) {
            // ����ͭ�ٻĤ����ֵٸ���
            $this->yukyu[0][1] -= round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
        }
    }

    // �Ķ�����μ�������
    public function getDayCount( $sday, $eday)
    {
        $query = "
                SELECT  tdate           AS ����,     -- 0
                        bd_flg          AS �Ķ���,   -- 1
                        note            AS ������  -- 2
                FROM    company_calendar
                WHERE   tdate >= '{$sday}' AND tdate <= '{$eday}' ORDER BY tdate
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
            return 0;
        }
        $cnt = 0;
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][1] == 't' ) {
                $cnt++;
            }
        }
        return $cnt;
    }

    // ����ñ�̤μ������֤����
    public function getTimeCount( $stime, $etime)
    {
        $t_hiru = $t_kyukei = 0;
        $t_1200 = strtotime('12:00');
        $t_1245 = strtotime('12:45');
        $t_1500 = strtotime('15:00');
        $t_1510 = strtotime('15:10');
        $t_str = strtotime($stime);
        $t_end = strtotime($etime);
        if( $t_str < $t_1200 &&  $t_end > $t_1245 ) $t_hiru = ($t_1245 - $t_1200);      // 45
        if( $t_str < $t_1500 &&  $t_end > $t_1510 ) $t_kyukei = ($t_1510 - $t_1500);    // 10
        $t_end -= ($t_hiru + $t_kyukei);
        $t_cnt = ($t_end - $t_str) / 60 / 60;
        return $t_cnt;
    }

    // ������Ͽ����Ƥ��롢AMȾ��ͭ��ٲˡ�����ñ��ͭ��ٲ�(12:45��)�Υǡ�������
    // [����]������˥����å������
    public function getAMandTimeVacationData()
    {
        $query = "
            SELECT      content, start_date, end_date
            FROM        sougou_deteils
            WHERE       uid='{$this->syainbangou}' AND (content='AMȾ��ͭ��ٲ�' OR (content='����ñ��ͭ��ٲ�' AND start_time = '12:45'))
                    AND admit_status!='CANCEL' AND admit_status!='DENY'
            ORDER BY    start_date ASC
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return false;
        }

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return true;
    }

    // ɽ������٤Υǡ��������
    public function getViewDataList(&$result)
    {
        $query = "
            SELECT      uid, name, sm.section_name
            FROM        user_detailes  AS ud
            LEFT JOIN   section_master AS sm
            ON          ud.sid = sm.sid
            WHERE       uid = '{$this->syainbangou}' AND retire_date IS NULL
            ORDER BY    uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
            $this->syain = '';
            return false;
        }
        $result->add_array($res);
        return $rows;
    }

    // ���� or ����Ұ� �Ǥ�����
    public function IsKeiyaku($uid)
    {
        $query = "
            SELECT          pid
            FROM            user_detailes
            WHERE           uid = '$uid' AND (pid=8 OR pid=9)
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // ��Ĺ����Ĺ���� �ʤ�
    public function IsKatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->syainbangou' AND (ud.pid=46 OR ud.pid=50 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // ��Ĺ�����ʾ塩 95=������Ĺ
    public function IsBukatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->syainbangou' AND (ud.pid=46 OR ud.pid=47 OR ud.pid=50 OR ud.pid=70 OR ud.pid=95 )
        ";
        $res = array();

        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }

        return true;
    }

    // ��ǧ�롼�ȼ���
    public function getApprovalPath(&$request, $uid)
    {
        $get_qry = "
            SELECT
                ct.act_id, apm.kakarityo, apm.katyo, apm.butyo, apm.somukatyo, apm.kanributyo, apm.kojyotyo
            FROM
                cd_table                AS ct
            LEFT JOIN
                approval_path_master    AS apm
            ON
                ct.act_id = apm.act_id
            WHERE
                uid = '$uid'
            LIMIT 1
        ";
        $res = array();
        if( getResultWithField2($get_qry, $field, $res) <= 0 ) {
            $this->approvalpath = "��ǧ��ϩ�ޥ����������˼��Ԥ��ޤ�����";
            return false;
        }

        $max = count($field);
        // ��ʬ�μҰ��ֹ�ν�ϥ����åפ��롣
        for( $i=0; $i<$max; $i++) {
            if( $res[0][$i] == $uid ) $res[0][$i] = '------';
        }

        $get_qry2 = "
            SELECT
                standards_date      AS �����
                ,somukatyo           AS ��̳��Ĺ
                ,kanributyo          AS ������Ĺ
                ,kojyotyo            AS ����Ĺ
            FROM
                approval_path_master_Late
            WHERE
                standards_date <= CURRENT_TIMESTAMP
            ORDER BY
                standards_date DESC
            LIMIT 1
        ";
        $res2 = array();

        if( getResultWithField2($get_qry2, $field2, $res2) <= 0 ) {
            $this->approvalpath = "��ǧ��ϩ�ޥ������ʸ�Ⱦ��ʬ�˼������Ԥ��ޤ�����";
            return false;
        }

        // ��ǧ��ϩ�ޥ��������'on'�ν�ˡ���̳��Ĺ������Ĺ�ޤǤμҰ������ɤ򥻥å�
        for( $i=4; $i<$max; $i++ ) {
            if( trim($res[0][$i]) == 'on' ) $res[0][$i] = $res2[0][$i-3];
        }

        $request->add("act_id", $res[0][0]);
        $request->add("kakarityo", $res[0][1]);
        $request->add("katyo", $res[0][2]);
        $request->add("butyo", $res[0][3]);
        $request->add("somukatyo", $res[0][4]);
        $request->add("kanributyo", $res[0][5]);
        $request->add("kojyotyo", $res[0][6]);

        $app_path = '';

        if( $this->IsBukatyou() ){    // ��Ĺ��������Ĺ����Ĺ��������Ĺ�ϡ����˹���Ĺ��ǧ�ˤʤ롣
            if( $res[0][6] != '------' ) {
                $app_path .= "�� �� Ĺ";
            } else {
                $this->approvalpath = "���������ɡ�{$res[0][0]}�˹���Ĺ ��Ͽ �ʤ��������Ԥ�Ϣ���Ʋ�������";
                return false;
            }
            $max--; // ����Ĺ�ϴ��˥��åȤ��Ƥ���١������-1���롣
        }
        if( $res[0][6] == $uid ) {
            $max--; // ����Ĺ�ϴ��˥��åȤ��Ƥ���١������-1���롣
        }

        for( $i=1; $i<$max; $i++ ) {
//$_SESSION['s_sysmsg'] .= "[{$i}]:{$res[0][$i]} ";
            if( is_numeric(($res[0][$i])) ) {
                if($app_path != "") $app_path .= " �� ";

                switch ($i) {
                case 1:
                case 2:
                case 3:
                    $app_path .= $this->getSyainName($res[0][$i]);
                    break;
                case 4:
                    $app_path .= "��̳��Ĺ";
                    break;
                case 5:
                    $app_path .= "������Ĺ";
                    break;
                case 6:
                    $app_path .= "�� �� Ĺ";
                    break;
                }
            }
        }

        if( $app_path == '' ) {
            $this->approvalpath = "���������ɡ�{$res[0][0]}����Ͽ �ʤ��������Ԥ�Ϣ���Ʋ�������";
            return false;
        }

        $this->approvalpath = $app_path;
        return true;
    }

    // �Ұ��ν�°�����
    public function getSyozoku($uid)
    {
        ///// ��� $partsKey �ե�����ɤǤθ���
        $query = "
            SELECT      sm.section_name
            FROM        user_detailes  AS ud
            LEFT JOIN   section_master AS sm
            ON          ud.sid = sm.sid
            WHERE       uid = '$uid'
            ORDER BY    uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
            return "------";
        }
        return $res[0][0];
    }

    // �Ұ�̾����
    public function getSyainName($str)
    {
            $query = "
                SELECT      name
                FROM        user_detailes
                WHERE       uid = '$str'
            ";
            $res = array();
            if ( $this->getResult2($query, $res) <= 0 ) {
                return '';
            }
            return $res[0][0];
    }

    // ������������Ϥ��Ф��뾵ǧ�롼�Ȥ�DB����Ͽ
    public function setApprovalPath($request)
    {
            $date                   = $request->get("sin_date");        // ����ǯ����
            $uid                    = $request->get("syain_no");        // ������ �Ұ��ֹ�
            $this->getApprovalPath($request, $uid);
            $act_id                 = $request->get("act_id");
            $kakarityo              = $request->get("kakarityo");
            $katyo                  = $request->get("katyo");
            $butyo                  = $request->get("butyo");
            $somukatyo              = $request->get("somukatyo");
            $kanributyo             = $request->get("kanributyo");
            $kojyotyo               = $request->get("kojyotyo");

            $insert_qry = "
                INSERT INTO approval_path
                (date, uid, act_id, kakarityo, katyo, butyo, somukatyo, kanributyo, kojyotyo)
                VALUES
                ('$date', '$uid', '$act_id', '$kakarityo', '$katyo', '$butyo', '$somukatyo', '$kanributyo', '$kojyotyo');
            ";
            if( query_affected($insert_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "��ϩ����Ͽ�˼��Ԥ��ޤ�����";
                return false;
            }
        return true;
    }

    // ��޻��᡼������
    public function hurryMaile($admit_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

        //$search_m = "WHERE uid='300144'";
        //$search_m = "WHERE uid='300667'";
        // ��ϥƥ����� ����Ū�˼�ʬ�˥᡼�������
        $search_m = "WHERE uid='$admit_uid'";

        $query_m = sprintf("$query_m %s", $search_m);     // SQL query ʸ�δ���
        $res_m   = array();
        $field_m = array();
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "���衧 {$sendna} �� �ڻ�ޡ�����Ͼ�ǧ�Τ��Τ餻";
            //�ƥ����� ��å�����
            //$message  = "{$admit_uid}�� ��ޤ�����Ϥ�����ޤ���\n\n";
            //�ƥ����� �����ѹ����뤳��
            $message  = "��ޤ�����Ϥ�����ޤ���\n\n";
            $message .= "����Ϥξ�ǧ�����򤪴ꤤ���ޤ���\n\n";
            $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid=";
            $message .= $admit_uid;
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // ���ʼԤؤΥ᡼�������������¸
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
        }
    }

    // ����Ϥο������Ƥ�DB����Ͽ
    public function add($request)
    {
        ///// �ѥ�᡼������ʬ��
        $date                   = $request->get("sin_date");        // ����ǯ����
        $uid                    = $request->get("syain_no");        // ������ �Ұ��ֹ�
        $start_date             = $request->get("str_date");        // ���� ���� ����
        $start_time             = $request->get("str_time");        // ���� ���� ����
        $end_date               = $request->get("end_date");        // ���� ��λ ����
        $end_time               = $request->get("end_time");        // ���� ��λ ����
        $content                = $request->get('r1');              // ���ơʥ饸��1��
        $yukyu                  = $request->get('r2');              // ���ơʥ饸��2��ͭ�ٴ�Ϣ
        $ticket01               = $request->get('r3');              // ���ơʥ饸��3�˾�ַ�
        $ticket02               = $request->get('r4');              // ���ơʥ饸��4�˿�����������
        $special                = $request->get('r5');              // ���ơʥ饸��5�����̴�Ϣ
        $others                 = $request->get('ikisaki');         // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('tokubetu_sonota'); // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('hurikae');         // ���ơ�ʸ����1�˹��衦���ص���������¾
        if( $others == '' )
            $others             = $request->get('syousai_sonota');  // ���ơ�ʸ����1�˹��衦���ص���������¾

        $place                  = $request->get('todouhuken');      // ���ơ�ʸ����2����ƻ�ܸ�
        $purpose                = $request->get('mokuteki');        // ���ơ�ʸ����3����Ū
        $ticket01_set           = $request->get('setto1');          // ���ơ�ʸ����4�˾�ַ����åȿ�
        $ticket02_set           = $request->get('setto2');          // ���ơ�ʸ����5�˿��������åȿ�
        $doukousya              = $request->get('doukou');          // ���ơ�ʸ����6��Ʊ�Լ�
        if( $doukousya == '' )
            $doukousya             = '---';                         // ���ơ�ʸ����6��Ʊ�Լ�
        $remarks                = $request->get('bikoutext');       // ����
        if( $remarks == '' )
            $remarks             = '---';                           // ����

        $contact                = $request->get('r6');              // Ϣ����ʥ饸����
        if( $contact == '' )
            $contact             = '---';                           // Ϣ����ʥ饸����
        $contact_other          = $request->get('tel_sonota');      // Ϣ����ʤ���¾��
        $contact_tel            = $request->get('tel_no');          // Ϣ�����TEL��
        $received_phone         = '';                               // ���żԡʥ����å���
        $received_phone_date    = $request->get('jyu_date');        // ���żԡ�������
        $received_phone_name    = $request->get('outai');           // ���żԡʱ��мԡ�
        if( $received_phone_name) $received_phone = '���ż�';       // ���мԤ�������������å�����żԤˤ���

        $hurry                  = $request->get('c2');              // ��ޡʥ����å���

        $jyuden_skip = false; // ��Ĺ��������Ĺ �λ��忽���ξ����˾�Ĺ(��Ĺ)

//        if( ($ticket01 != "" && $ticket01 != "����") || ($ticket02 != "" && $ticket02 != "����") ) {
        if( $ticket01 == "��ƻ" || $ticket01 == "����" || $ticket02 == "��ƻ" || $ticket02 == "����" ) {
            $ticket             = true;     // �������ͭ/̵
        } else {
            $ticket             = false;    // �������ͭ/̵
        }
        $approval_status        = 0;        // ��ǧ����
        $amano_input            = 0;        // ���ޥ����Ϥ�̵ͭ

        if( $content == "ͭ��ٲ�" || $content == "AMȾ��ͭ��ٲ�" || $content == "PMȾ��ͭ��ٲ�" ||
            $content == "����ñ��ͭ��ٲ�" || $content == "���"  || $content == "�ٹ�����" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, yukyu, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$yukyu', '$remarks');
            ";
        } else if( $content == "��ĥ���������" || $content == "��ĥ�ʽ����"
            || $content == "ľ��" || $content == "ľ��" || $content == "ľ��/ľ��" ) {
            if( !$ticket ) {
                $ticket = 'f';
            }
            if(!$ticket01) $ticket01 = "����";
            if(!$ticket02) $ticket02 = "����";
            if( $content == "ľ��" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            } else if( $content == "ľ��" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, end_date, end_time, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$end_date', '$end_time', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            } else {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content,
                     ticket01, ticket02, others, place, purpose, doukousya, remarks, ticket)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content',
                     '$ticket01', '$ticket02', '$others', '$place','$purpose', '$doukousya', '$remarks', '$ticket');
                ";
            }
            if( !$ticket01_set && !$ticket02_set ) $ticket = false;
        } else if( $content == "���̵ٲ�" ) {
            if( $special != "����¾" ) {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content, special, remarks)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$special', '$remarks');
                ";
            } else {
                $insert_qry = "
                    INSERT INTO sougou_deteils
                    (date, uid, start_date, start_time, end_date, end_time, content, special, others, remarks)
                    VALUES
                    ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$special', '$others', '$remarks');
                ";
            }
        } else if( $content == "���ص���" || $content == "����¾" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, others, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$others', '$remarks');
            ";
        } else if( $content == "ID�������̤�˺��ʽжС�" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$content', '$remarks');
            ";
            $jyuden_skip = true; // ID�����ɷ�
        } else if( $content == "ID�������̤�˺�����С�" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$end_date', '$end_time', '$content', '$remarks');
            ";
            $jyuden_skip = true; // ID�����ɷ�
        } else if( $content == "���¾�ǧ˺��ʻĶȿ���ϳ���" || $content == "ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���" ) {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$remarks');
            ";
            $jyuden_skip = true; // ID�����ɷ�
        } else {
            $insert_qry = "
                INSERT INTO sougou_deteils
                (date, uid, start_date, start_time, end_date, end_time, content, remarks)
                VALUES
                ('$date', '$uid', '$start_date', '$start_time', '$end_date', '$end_time', '$content', '$remarks');
            ";
        }

        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "��Ͽ�˼��Ԥ��ޤ�����";
            return false;
        }

        if( $ticket ) {
            if( $ticket01 != "����" && $ticket02 != "����") {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $ticket02_set, $date, $uid);
            } else if( $ticket01 != "����" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $date, $uid);
            } else if( $ticket02 != "����" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket02_set, $date, $uid);
            } else {
                $update_qry = "";
            }
            if( $update_qry != "" && query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "���������Ͽ�˼��Ԥ��ޤ�����";
            }
        }

        if( $contact == "����¾" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_other, $contact_tel, $date, $uid);
        } else if( $contact == "��ĥ��" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_tel, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s' WHERE date='%s' AND uid='%s'", $contact, $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "Ϣ�������Ͽ�˼��Ԥ��ޤ�����";
        }

        if( $received_phone == "���ż�" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='%s', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone, $received_phone_date, $received_phone_name, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "���żԤ���Ͽ�˼��Ԥ��ޤ�����";
            }
        }

        $this->setApprovalPath($request);

        if( $this->IsKatyou() ) {           // ��Ĺ��������Ĺ �ϡ����˹���Ĺ��ǧ�ˤʤ롣
            $approval              = $request->get("kojyotyo");
/**/
            $sin_dt = new DateTime($date);                          // ��������
            $end_dt = new DateTime("{$end_date} {$end_time}");      // �о�����(��λ)
            if( $sin_dt >= $end_dt && !$jyuden_skip ) {
                $approval          = $request->get("butyo");
            }
/**/
        } else if( $this->IsBukatyou() ) {  // ��Ĺ��������Ĺ�ϡ����˹���Ĺ��ǧ�ˤʤ롣
            $approval              = $request->get("kojyotyo");
        } else {
            $approval              = $request->get("kakarityo");
        }
        if( !is_numeric($approval) ) $approval = $request->get("katyo");
        if( !is_numeric($approval) ) $approval = $request->get("butyo");
        if( !is_numeric($approval) ) $approval = $request->get("somukatyo");
        if( !is_numeric($approval) ) $approval = $request->get("kanributyo");
        if( !is_numeric($approval) ) $approval = $request->get("kojyotyo");

        if( $approval != '' ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET admit_status='%s' WHERE date='%s' AND uid='%s'", $approval, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$approval ��ǧ�����Ͽ�˼��Ԥ��ޤ�����";
            }
        }

        if( $hurry == "���" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry='%s' WHERE date='%s' AND uid='%s'", $hurry, $date, $uid);
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "��ޤ���Ͽ�˼��Ԥ��ޤ�����";
            }
            $this->hurryMaile($approval);
        }

        if( $request->get('reappl') == 'on' ) {
            $deny_uid = $request->get("deny_uid");
            $previous_date = $request->get("previous_date");
            $insert_qry = "
                INSERT INTO sougou_reappl
                (date, uid, reappl, deny_uid, re_date)
                VALUES
                ('$previous_date', '$uid', 't', '$deny_uid', '$date');
            ";
            if( query_affected($insert_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] = "�ƿ�����DB��Ͽ�˼��Ԥ��ޤ�����";
            }
        }

    }

    // �ƿ�����ǽ�Ǥ�����
    public function IsReApplPossible($request)
    {
        $date = str_replace('@',' ', $request->get("date"));
        $uid = $request->get("syainbangou");

        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   date='{$date}' AND uid='{$uid}';
            ";
        if( getResult2($query, $res) <= 0 ) {
            return true;    // ��ǽ
        }
        return false;       // ���˿����Ѥ�
    }

    // �ƿ����ΰ�ɽ������ǡ��������
    public function GetReViewData(&$request)
    {
        $date = str_replace('@',' ', $request->get("date"));

        $query = "
            SELECT      *
            FROM        sougou_deteils
            WHERE       date='{$date}'
                    AND uid='{$request->get("syainbangou")}'
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return false;
        }

//        $request->add("sin_date", $res[0][0]);              // ����ǯ����
        $request->add("syain_no", $res[0][1]);              // ������ �Ұ��ֹ�
        $res[0][2] = str_replace('-','', $res[0][2]);
        $request->add("str_date", $res[0][2]);              // ���� ���� ����
        $request->add("str_time", $res[0][3]);              // ���� ���� ����
        $res[0][4] = str_replace('-','', $res[0][4]);
        $request->add("end_date", $res[0][4]);              // ���� ��λ ����
        $request->add("end_time", $res[0][5]);              // ���� ��λ ����
        $request->add('r1', trim($res[0][6]));              // ���ơʥ饸��1��
        $request->add('r2', trim($res[0][7]));              // ���ơʥ饸��2��ͭ�ٴ�Ϣ
        $request->add('r3', trim($res[0][8]));              // ���ơʥ饸��3�˾�ַ�
        $request->add('r4', trim($res[0][9]));              // ���ơʥ饸��4�˿�����������
        $request->add('r5', trim($res[0][10]));             // ���ơʥ饸��5�����̴�Ϣ
        $res[0][11] = trim($res[0][11]);

        if( $request->get('r1') == "���̵ٲ�" ) {
            $request->add('tokubetu_sonota', $res[0][11]);  // ���ơ�ʸ����1�����̴�Ϣ����¾
        } else if( $request->get('r1') == "���ص���" ) {
            $request->add('hurikae', $res[0][11]);          // ���ơ�ʸ����1�˿��ص���
        } else if( $request->get('r1') == "����¾" ) {
            $request->add('syousai_sonota', $res[0][11]);   // ���ơ�ʸ����1�ˤ���¾
        } else {
            $request->add('ikisaki', $res[0][11]);          // ���ơ�ʸ����1�˹���
        }

        $request->add('todouhuken', trim($res[0][12]));     // ���ơ�ʸ����2����ƻ�ܸ�
        $request->add('mokuteki', trim($res[0][13]));       // ���ơ�ʸ����3����Ū
        $request->add('setto1', trim($res[0][14]));         // ���ơ�ʸ����4�˾�ַ����åȿ�
        $request->add('setto2', trim($res[0][15]));         // ���ơ�ʸ����5�˿��������åȿ�
        $request->add('doukou', trim($res[0][16]));         // ���ơ�ʸ����6��Ʊ�Լ�
        if( trim($res[0][17]) == '---' ) $res[0][17] = '';
        $res[0][17] = trim($res[0][17]) . '(' . substr($request->get('date'), 0, 10) . " �����κƿ���)";
        $request->add('bikoutext', $res[0][17]);            // ����
        $request->add('r6', trim($res[0][18]));             // Ϣ����ʥ饸����
        $request->add('tel_sonota', trim($res[0][19]));     // Ϣ����ʤ���¾��
        $request->add('tel_no', trim($res[0][20]));         // Ϣ�����TEL��

        $request->add('received', $res[0][21]);             // ���ż�
        $request->add('jyu_date', $res[0][22]);             // ������
        $request->add('outai', trim($res[0][23]));          // ���м�

        $request->add('c2', $res[0][24]);                   // ��ޡʥ����å���
        $request->add('ticket', $res[0][25]);

        $request->add('reappl', 'on');                      // �ƿ����ե饰
        $request->add('previous_date', $date);              // ���ο�������

        return true;
    }

    // �����μ�ä�ɽ������ǡ��������
    public function GetDelViewData(&$request)
    {
        $date = str_replace('@',' ', $request->get("date"));

        $query = "
            SELECT      reason              -- ��ǧ��ͳ
            FROM        admit_stop_reason
            WHERE       date='{$date}'
                    AND uid='{$request->get("syainbangou")}'
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            return "";
        }

        return $res[0][0];
    }

    // ��å᡼������
    public function DelReasonMail($request)
    {
        $deny_uid = $request->get("deny_uid"); //��ǧ��No.
//$deny_uid = '300667';// �ƥ�����

        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

        //$search_m = "WHERE uid='300144'";
        //$search_m = "WHERE uid='300667'";
        // ��ϥƥ����� ����Ū�˼�ʬ�˥᡼�������
        $search_m = "WHERE uid='$deny_uid'";

        $query_m = sprintf("$query_m %s", $search_m);     // SQL query ʸ�δ���
        $res_m   = array();
        $field_m = array();
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "���衧 {$sendna} �� ����ϡڼ�áۤΤ��Τ餻";
            // ��å�����
            $name = trim($this->getSyainName($request->get("syainbangou")));
            $message  = "{$name} �ͤ����ʲ�����ͳ�ˤ������Ϥο������겼���ޤ�����\n\n";
            $message .= "�ڼ����ͳ��\n{$request->get('del_reason')}";
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // ���ʼԤؤΥ᡼�������������¸
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
            $this->DelReasonSave($request);
        }
    }

    // ��ò�ǽ�Ǥ�����
    public function IsDelPossible($request)
    {
        $date = str_replace('@',' ', $request->get("date"));
        $uid = $request->get("syainbangou");

        $query = "
                SELECT  *
                FROM    sougou_del_reason
                WHERE   date='{$date}' AND uid='{$uid}';
            ";
        if( getResult2($query, $res) <= 0 ) {
            return true;    // ��ǽ
        }
        return false;       // ���˼�úѤ�
    }

    // �����ͳ��DB����¸
    public function DelReasonSave($request)
    {
        $date = str_replace('@',' ', $request->get('date'));
        $uid = $request->get('syainbangou');
        $reason = $request->get('del_reason');

        $insert_qry = "
            INSERT INTO sougou_del_reason
            (date, uid, reason)
            VALUES
            ('$date', '$uid', '$reason');
        ";
        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "�����ͳ��DB��Ͽ�˼��Ԥ��ޤ�����";
        }
    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class Sougou_Model End

?>
