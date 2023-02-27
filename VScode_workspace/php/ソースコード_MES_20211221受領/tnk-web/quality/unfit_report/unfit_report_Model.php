<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                                                            MVC Model ��  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Model.php                              //
// 2008/08/29 masterst���ܲ�ư����                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class


/******************************************************************************
*     ��Ŭ�������� MVC��Model�� base class ���쥯�饹�����     *
******************************************************************************/
class UnfitReport_Model extends ComTableMnt
{
    ////////// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ////// �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        $listSpan   = $request->get('listSpan');
        
        switch ($request->get('showMenu')) {
        case 'Group':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM (SELECT count(group_no) FROM meeting_mail_group GROUP BY group_no {$this->where})
                AS meeting_group
            ";
            break;
        case 'CompleteList':
        case 'Apend' :
        case 'Edit'  :
        case 'Print' :
        case 'Follow':
        default      :
            $this->where = "WHERE occur_time>='{$year}-{$month}-{$day}' AND occur_time<=(timestamp '{$year}-{$month}-{$day}' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM unfit_report_header {$this->where}
            ";
            break;
        }
        ////// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ////// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'unfit_report.log');
    }
    
    ////////// ��Ŭ��������ɲ�
    public function add($request)
    {
        ////// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');            // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');              // ȯ��ǯ������������
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // ��Ŭ������ �����Ѵ�
        $request->add('subject', $subject);
        $place      = $request->get('place');               // ȯ�����
        $section    = $request->get('section');             // ��Ǥ����
        $assy_no    = $request->get('assy_no');             // �����ֹ�
        $parts_no   = $request->get('parts_no');            // �����ֹ�
        $sponsor    = $request->get('sponsor');             // ������
        $atten      = $request->get('atten');               // �����(attendance) (����)
        $mail       = $request->get('mail');                // �᡼������� Y/N
        ////// ǯ�����Υ����å�  ���ߤ� Main Controller�ǽ���ͤ����ꤷ�Ƥ���Τ�ɬ�פʤ��������Τޤ޻Ĥ���
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        $serial_no = $this->add_execute($request);
        if ($serial_no) {
            if ($mail == 't') {
                if ($this->guideUnfitMail($request, $serial_no)) {
                    $_SESSION['s_sysmsg'] = '�᡼����������ޤ�����';
                } else {
                    $_SESSION['s_sysmsg'] = '�᡼�������Ǥ��ޤ���Ǥ�����';
                }
            }
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
        }
        return false;
    }
    
    ////////// �ե������åפ����Ϥ��ɲ�
    public function follow($request)
    {
        ////// �ѥ�᡼������ʬ��
        $serial_no      = $request->get('serial_no');           // ���ꥢ���ֹ�
        $follow_section = $request->get('follow_section');      // �ե������å� ȯ������
        $follow_quality = $request->get('follow_quality');      // �ե������å� �ʼ��ݾڲ�
        $follow_opinion = $request->get('follow_opinion');      // �ե������å� �ո�
        $follow         = $request->get('follow');              // �ե������å״�λ Y/N
        $sponsor        = $request->get('sponsor');             // ������
        $atten          = $request->get('atten');               // �����(attendance) (����)
        $mail           = $request->get('mail');                // �᡼������� Y/N
        $serial_no = $this->follow_execute($request);
        if ($serial_no) {
            if ($mail == 't') {
                if ($this->guideFollowMail($request, $serial_no)) {
                    $_SESSION['s_sysmsg'] = '�᡼����������ޤ�����';
                } else {
                    $_SESSION['s_sysmsg'] = '�᡼�������Ǥ��ޤ���Ǥ�����';
                }
            }
            return true;
        } else {
            $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
        }
        return false;
    }
    
    ////////// ��Ŭ������δ������
    public function delete($request)
    {
        ////// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');           // ���ꥢ���ֹ�
        $subject    = $request->get('subject');             // ��Ŭ������
        $mail       = $request->get('mail');                // �᡼������� Y/N
        ////// �о���Ŭ�������¸�ߥ����å�
        $chk_sql = "
            SELECT subject FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // ����Υ��ꥢ���ֹ��¸�ߥ����å�
            $_SESSION['s_sysmsg'] = "��{$subject}�פ�¾�οͤ��ѹ�����ޤ�����";
        } else {
            if ($mail == 't') {
                if ($this->guideUnfitMail($request, $serial_no, true)) {
                    $_SESSION['s_sysmsg'] = '����󥻥�Υ᡼����������ޤ�����';
                } else {
                    $_SESSION['s_sysmsg'] = '����󥻥�Υ᡼���������Ǥ��ޤ���Ǥ�����';
                }
            }
            $response = $this->del_execute($serial_no, $subject);
            if ($response) {
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '����Ǥ��ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ��Ŭ��������ѹ�
    public function edit($request)
    {
        ////// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');           // Ϣ��(�����ե������)
        $year       = $request->get('yearReg');             // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');            // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');              // ȯ��ǯ����ͽ���������
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // ��Ŭ������ 2005/12/27 �����Ѵ��ɲ�
        $request->add('subject', $subject);
        $mail       = $request->get('mail');                // �᡼������� Y/N
        $reSend     = $request->get('reSend');              // �ѹ����Υ᡼��κ�����Yes/No
        ////// ǯ�����Υ����å�
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        
        $query = "
            SELECT subject FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $check) > 0) {  // �ѹ����Υ��ꥢ���ֹ椬��Ͽ����Ƥ��뤫��
            $response = $this->edit_execute($request);
            if ($response) {
                if ($reSend == 't' && $mail == 't') {
                    if ($this->guideUnfitMail($request, $serial_no)) {
                        $_SESSION['s_sysmsg'] = '�᡼�����������ޤ�����';
                    } else {
                        $_SESSION['s_sysmsg'] = '�᡼��κ��������Ǥ��ޤ���Ǥ�����';
                    }
                }
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
            }
        } else {
            $_SESSION['s_sysmsg'] = "��{$subject}�פ�¾�οͤ��ѹ�����ޤ�����";
        }
        return false;
    }
    
    ////////// ����襰�롼�פ���Ͽ���ѹ�
    public function group_edit($group_no, $group_name, $atten, $owner)
    {
        ////// group_no��Ŭ�������å�
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT owner, group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // ���롼�פ���Ͽ
            $response = $this->groupInsert($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ����Ͽ���ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '����襰�롼�פ���Ͽ������ޤ���Ǥ�����';
            }
        } else {
            // ���롼�פ��ѹ�
            // �ǡ������ѹ�����Ƥ��뤫�����å�
            // $atten[]�����󤬤��뤿���ά����
            // ���礬Ʊ���������å�
            if ($res[0][0] != '000000' && $res[0][0] != $_SESSION['User_ID']) {
                $_SESSION['s_sysmsg'] = '�ĿͤΥ��롼����Ͽ�Ǥ��� �ѹ��Ǥ��ޤ���';
                return false;
            }
            // ���롼�פ��ѹ� �¹�
            $response = $this->groupUpdate($group_no, $group_name, $atten, $owner);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ���ѹ����ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '����襰�롼�פ��ѹ�������ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ����襰�롼�פ� ���
    public function group_omit($group_no, $group_name)
    {
        ////// group_no��Ŭ�������å�
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} �Ϻ���оݥǡ���������ޤ���";
        } else {
            // ������Ƥ�����ʤ������Υǡ���������å��Ϻ����ɬ�פʤ�
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
    
    ////////// ����襰�롼�פ� ͭ����̵��
    public function group_activeSwitch($group_no, $group_name)
    {
        ////// group_no��Ŭ�������å�
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} ���оݥǡ���������ޤ���";
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
                SELECT active FROM meeting_mail_group WHERE group_no={$group_no}
            ";
            $update_sql = "
                UPDATE meeting_mail_group SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE group_no={$group_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ////////// List��
    public function getViewList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                         -- 10
                ,to_char(occur_time, 'YYYY')              -- 11
                ,to_char(occur_time, 'MM')                -- 12
                ,to_char(occur_time, 'DD')                -- 13
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 14
                ,measure                                -- 15
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// CompleteList��
    public function getViewCompleteList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                        -- 10
                ,to_char(occur_time, 'YYYY')            -- 11
                ,to_char(occur_time, 'MM')              -- 12
                ,to_char(occur_time, 'DD')              -- 13
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 14
                ,measure                                -- 15
                ,to_char(follow_when, 'YY/MM/DD')       -- 16
                ,follow_sponsor                         -- 17
                ,follow                                 -- 18
                ,to_char(unfit_report_follow.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 19
                ,to_char(follow_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 20
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                unfit_report_follow USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 't' AND follow = 'f'
            ORDER BY    
                follow_when ASC, occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $serial = $res[$i][0];
            $query_f = "
                SELECT serial_no                            -- 00
                    ,follow_sponsor                         -- 01
                    ,trim(name)             AS ��̾         -- 02
                FROM
                    unfit_report_follow AS unfit
                LEFT OUTER JOIN
                    user_detailes ON (follow_sponsor=uid)
                WHERE
                    serial_no = '$serial'
                ORDER BY
                    serial_no ASC
            ";
            $res_f = array();
            // �ե������å׺����Ԥ�̾����$res�κǸ���ɲ�
            if ( ($rows_f=$this->execute_List($query_f, $res_f)) < 1 ) {
                $res[$i][21] = '';
            } else {
                $res[$i][21] = $res_f[0][2];
            }
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// IncompleteList��
    public function getViewIncompleteList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                         -- 10
                ,to_char(occur_time, 'YYYY')              -- 11
                ,to_char(occur_time, 'MM')                -- 12
                ,to_char(occur_time, 'DD')                -- 13
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 14
                ,measure                                -- 15
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 'f'
            ORDER BY
                occur_time DESC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// FollowList��
    public function getViewFollowList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(occur_time, 'YY/MM/DD')        -- 02
                ,section                                -- 03
                ,place                                  -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,to_char(unfit.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 08
                ,to_char(unfit.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,unfit.last_host                        -- 10
                ,to_char(occur_time, 'YYYY')            -- 11
                ,to_char(occur_time, 'MM')              -- 12
                ,to_char(occur_time, 'DD')              -- 13
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 14
                ,measure                                -- 15
                ,to_char(follow_when, 'YY/MM/DD')       -- 16
                ,follow_sponsor                         -- 17
                ,follow                                 -- 18
                ,to_char(follow_date AT TIME ZONE 'JST', 'YY/MM/DD')
                                                        -- 19
            FROM
                unfit_report_header AS unfit
            LEFT OUTER JOIN
                unfit_report_measure USING(serial_no)
            LEFT OUTER JOIN
                unfit_report_follow USING(serial_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            WHERE
                measure = 't' AND follow = 't'
            ORDER BY
                follow_date ASC, occur_time ASC, serial_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $serial = $res[$i][0];
            $query_f = "
                SELECT serial_no                            -- 00
                    ,follow_sponsor                         -- 01
                    ,trim(name)             AS ��̾         -- 02
                FROM
                    unfit_report_follow AS unfit
                LEFT OUTER JOIN
                    user_detailes ON (follow_sponsor=uid)
                WHERE
                    serial_no = '$serial'
                ORDER BY
                    serial_no ASC
            ";
            $res_f = array();
            // �ե������å׺����Ԥ�̾����$res�κǸ���ɲ�
            if ( ($rows_f=$this->execute_List($query_f, $res_f)) < 1 ) {
                $res[$i][20] = '';
            } else {
                $res[$i][20] = $res_f[0][2];
            }
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// ������ List�� attendance ʣ���б�
    public function getViewAttenList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,atten                                  -- 01
                ,trim(name)                             -- 02
                ,CASE
                    WHEN mail THEN '������'
                    ELSE '̤����'
                 END                                    -- 03
            FROM
                unfit_report_attendance AS unfit
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                serial_no = {$serial_no}
            ORDER BY
                atten ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// ������μҰ��ֹ�Ȼ�̾�����
    /*** userId_name ������֤�, atten ���� selected �������� ***/
    public function getViewUserName(&$userID_name, $atten)
    {
        $query = "
            SELECT uid       AS �Ұ��ֹ�
                , trim(name) AS ��̾
            FROM
                user_detailes
            WHERE
                retire_date IS NULL
                AND
                sid != 31
            ORDER BY
                pid DESC, sid ASC, uid ASC
            
        ";
        $userID_name = array();
        if ( ($rows=$this->getResult2($query, $userID_name)) < 1 ) {
            $_SESSION['s_sysmsg'] = '�Ұ��ǡ�������Ͽ������ޤ���';
        }
        if (is_array($atten)) {
            $r = count($atten);
            for ($i=0; $i<$rows; $i++) {
                for ($j=0; $j<$r; $j++) {
                    if ($userID_name[$i][0] == $atten[$j]) {
                        $userID_name[$i][2] = ' selected';
                        break;
                    } else {
                        $userID_name[$i][2] = '';
                    }
                }
            }
        }
        return $rows;
        
    }
    
    ////////// Edit ���� 1�쥳����ʬ
    public function getViewEdit($serial_no, $result)
    {
        $query = "
            SELECT serial_no                    -- 00
                ,subject                        -- 01
                ,place                          -- 02
                ,section                        -- 03
                ,sponsor                        -- 04
                ,atten_num                      -- 05
                ,mail                           -- 06
                ,to_char(occur_time, 'YYYY')    -- 07
                ,to_char(occur_time, 'MM')      -- 08
                ,to_char(occur_time, 'DD')      -- 09
                ,receipt_no                     -- 10
            FROM
                unfit_report_header
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('serial_no',  $res[0][0]);
            $result->add_once('subject',    $res[0][1]);
            $result->add_once('place',      $res[0][2]);
            $result->add_once('section',    $res[0][3]);
            $result->add_once('sponsor',    $res[0][4]);
            $result->add_once('atten_num',  $res[0][5]);
            $result->add_once('mail',       $res[0][6]);
            $result->add_once('editYear',   $res[0][7]);
            $result->add_once('editMonth',  $res[0][8]);
            $result->add_once('editDay',    $res[0][9]);
            $result->add_once('receipt_no', $res[0][10]);
        }
        return $rows;
    }
    
    ////////// ȯ�������� List�� cause
    public function getViewCauseList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                          -- 00
                ,assy_no                              -- 01
                ,parts_no                             -- 02
                ,occur_cause                          -- 03
                ,unfit_num                            -- 04
                ,issue_cause                          -- 05
                ,issue_num                            -- 06
            FROM
                unfit_report_cause AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('assy_no',     $res[0][1]);
            $result->add_once('parts_no',    $res[0][2]);
            $result->add_once('occur_cause', $res[0][3]);
            $result->add_once('unfit_num',   $res[0][4]);
            $result->add_once('issue_cause', $res[0][5]);
            $result->add_once('issue_num',   $res[0][6]);
        }
        return $rows;
    }
    
    ////////// �к��� List�� cause
    public function getViewMeasureList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no                         -- 00
                ,unfit_dispose                       -- 01
                ,occur_measure                       -- 02
                ,to_char(occurMeasure_date, 'YYYY')  -- 03
                ,to_char(occurMeasure_date, 'MM')    -- 04
                ,to_char(occurMeasure_date, 'DD')    -- 05
                ,issue_measure                       -- 06
                ,to_char(issueMeasure_date, 'YYYY')  -- 07
                ,to_char(issueMeasure_date, 'MM')    -- 08
                ,to_char(issueMeasure_date, 'DD')    -- 09
                ,follow_who                          -- 10
                ,follow_when                         -- 11
                ,follow_how                          -- 12
                ,measure                             -- 13
            FROM
                unfit_report_measure AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('unfit_dispose', $res[0][1]);
            $result->add_once('occur_measure', $res[0][2]);
            $result->add_once('occurYear',     $res[0][3]);
            $result->add_once('occurMonth',    $res[0][4]);
            $result->add_once('occurDay',      $res[0][5]);
            $result->add_once('issue_measure', $res[0][6]);
            $result->add_once('issueYear',     $res[0][7]);
            $result->add_once('issueMonth',    $res[0][8]);
            $result->add_once('issueDay',      $res[0][9]);
            $result->add_once('follow_who',    $res[0][10]);
            $result->add_once('follow_when',   $res[0][11]);
            $result->add_once('follow_how',    $res[0][12]);
            $result->add_once('measure',       $res[0][13]);
        }
        return $rows;
    }
    
    ////////// Ÿ���� List�� develop
    public function getViewDevelopList(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no             -- 00
                ,suihei                  -- 01
                ,kanai                   -- 02
                ,kagai                   -- 03
                ,hyoujyun                -- 04
                ,kyouiku                 -- 05
                ,system                  -- 06
            FROM
                unfit_report_develop AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('suihei',    $res[0][1]);
            $result->add_once('kanai',     $res[0][2]);
            $result->add_once('kagai',     $res[0][3]);
            $result->add_once('hyoujyun',  $res[0][4]);
            $result->add_once('kyouiku',   $res[0][5]);
            $result->add_once('system',    $res[0][6]);
        }
        return $rows;
    }
    
    ////////// Ÿ���� List�� follow
    public function getViewFollow(&$result, $serial_no)
    {
        $query = "
            SELECT serial_no            -- 00
                ,follow_section         -- 01
                ,follow_quality         -- 02
                ,follow_opinion         -- 03
                ,follow_sponsor         -- 04
                ,follow                 -- 05
            FROM
                unfit_report_follow AS unfit
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('follow_section',    $res[0][1]);
            $result->add_once('follow_quality',    $res[0][2]);
            $result->add_once('follow_opinion',    $res[0][3]);
            $result->add_once('follow_sponsor',    $res[0][4]);
            $result->add_once('follow',            $res[0][5]);
            $result->add_once('follow_flg',        't');
        } else {
            $result->add_once('follow_section',    '');
            $result->add_once('follow_quality',    '');
            $result->add_once('follow_opinion',    '');
            $result->add_once('follow_sponsor',    '');
            $result->add_once('follow',            '');
            $result->add_once('follow_flg',        'f');
        }
        return $rows;
    }
    
    ////////// List���� ɽ��(����ץ����)������
    public function get_caption($switch, $year, $month, $day)
    {
        switch ($switch) {
        case 'List':
            $caption = '��';
            $caption = sprintf("%04dǯ%02d��%02d��{$caption}", $year, $month, $day);
            break;
        case 'Apend':
            $caption = '������ɲ�';
            break;
        case 'Edit':
            $caption = '������Խ�';
            break;
        case 'Follow':
            $caption = '�ե������åפ��Խ�';
            break;
        default:
            $caption = '';
        }
        return $caption;
        
    }
    
    ////////// List���� ��Ͽ�ǡ������ʤ����Υ�å���������
    public function get_noDataMessage($year, $month, $day)
    {
        if ($year != '') {
            if (sprintf('%04d%02d%02d', $year, $month, $day) < date('Ymd')) {
                $noDataMessage = '���񤬤���ޤ���';  // ���ξ��
            } else {
                $noDataMessage = '���񤬤���ޤ���';  // ̤��ξ��
            }
        } else {
            // �����ξ��
            $noDataMessage = '���񤬤���ޤ���';
        }
        return $noDataMessage;
        
    }
    
    ////////// ����襰�롼�פ� List��
    public function getViewGroupList(&$result)
    {
        $query = "
            SELECT group_no                             -- 00
                ,group_name                             -- 01
                ,owner                                  -- 02
                ,CASE
                    WHEN active THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ͭ��̵��     -- 03
                ,to_char(mail.regdate, 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(mail.last_date, 'YY/MM/DD HH24:MI')
                                                        -- 05
                ,trim(name)                             -- 06
            FROM
                meeting_mail_group AS mail
            LEFT OUTER JOIN
                user_detailes ON (owner=uid)
            GROUP BY
                group_no, group_name, owner, active, mail.regdate, mail.last_date, name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// ����襰�롼�פ� �����롼��ʬ Attendance List��
    public function getGroupAttenList(&$result, $group_no)
    {
        $query = "
            SELECT
                 trim(name)                             -- 00
                ,atten                                  -- 01
            FROM
                meeting_mail_group
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                group_no={$group_no}
            ORDER BY
                pid DESC, sid ASC, uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            //$_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ////////// ����襰�롼�פ�ͭ���ʥꥹ�� Active List��
    ////////// JSgroup_name=���롼��̾�Σ���������, JSgroup_member=���롼��̾���б����������Σ���������, �����=ͭ�����
    ////////// owner='000000'�϶�ͭ���롼��, ���꤬������ϸĿͤΥ��롼��
    public function getActiveGroupList(&$JSgroup_name, &$JSgroup_member, $uid)
    {
        ////// �����
        $JSgroup_name = array();
        $JSgroup_member = array();
        ////// ���롼��̾������μ���
        $query = "
            SELECT group_name                             -- 00
                 , group_no                               -- 01
            FROM
                meeting_mail_group
            WHERE
                active AND (owner='000000' OR owner='{$uid}')
            GROUP BY
                group_no, group_name
            ORDER BY
                group_no ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return false;
        }
        for ($i=0; $i<$rows; $i++) {
            $JSgroup_name[$i] = $res[$i][0];
            // ���롼�ץ��С���2��������μ���
            $query = "
                SELECT
                     atten                             -- 00
                FROM
                    meeting_mail_group
                LEFT OUTER JOIN
                    user_detailes ON (atten=uid)
                WHERE
                    group_no={$res[$i][1]}
                ORDER BY
                    pid DESC, sid ASC, uid ASC
            ";
            $resMem = array();
            if ( ($rowsMem=$this->getResult2($query, $resMem)) < 1 ) {
                return false;
            }
            for ($j=0; $j<$rowsMem; $j++) {
                $JSgroup_member[$i][$j] = $resMem[$j][0];
            }
        }
        return $rows;
    }
    
    ////////// ����̾�ν���
    public function getTargetPartsNames($request)
    {
        $query = "
            SELECT
                midsc      AS ����̾
            FROM miitem
            WHERE mipn='{$request->get('parts_no')}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// �����
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "��";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// ����̾�ν���(EDIT��)
    public function getTargetPartsNamesEdit($parts_no)
    {
        $query = "
            SELECT
                midsc      AS ����̾
            FROM miitem
            WHERE mipn='{$parts_no}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// �����
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "��";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// ����̾�ν���
    public function getTargetAssyNames($request)
    {
        $query = "
            SELECT
                midsc      AS ����̾
            FROM miitem
            WHERE mipn='{$request->get('assy_no')}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// �����
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "��";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    ////////// ����̾�ν���(EDIT��)
    public function getTargetAssyNamesEdit($assy_no)
    {
        $query = "
            SELECT
                midsc      AS ����̾
            FROM miitem
            WHERE mipn='{$assy_no}'
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        ////// �����
        $option = "\n";
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $name = "��";
        } else {
            $name = "{$res[0][0]}";
        }
        return $name;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// group_no��Ŭ��������å�����å������ܷ��(true=OK,false=NG)���֤�
    protected function checkGroupNo($group_no)
    {
        ////// group_no��Ŭ�������å�
        if (is_numeric($group_no)) {
            if ($group_no >= 1 && $group_no <= 999) {   // int2 ���⤬�ºݤ��ϰ�
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "���ʼԤΥ��롼���ֹ� {$group_no} ���ϰϳ��Ǥ��� 1��999�ޤǤǤ���";
            }
        } else {
            $_SESSION['s_sysmsg'] = "���ʼԤΥ��롼���ֹ� {$group_no} �Ͽ����ʳ����ޤޤ�Ƥ��ޤ���";
        }
        return false;
    }
    ////////// ��Ŭ������ΰ���� email �ǽФ���
    protected function guideUnfitMail($request, $serial_no, $cancel=false)
    {
        ////// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');            // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');              // ȯ��ǯ������������
        $subject    = $request->get('subject');             // ��Ŭ������
        $sponsor    = $request->get('sponsor');             // ������
        $atten      = $request->get('atten');               // �����(attendance) (����)
        $place      = $request->get('place');               // ȯ�����
        $section    = $request->get('section');             // ��Ǥ����
        $atten_num  = count($atten);                        // ������
        $mail       = $request->get('mail');                // �᡼������� Y/N
        ////// �������������
        $week = array('��', '��', '��', '��', '��', '��', '��');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        ////// �����Ԥ�̾�������
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "�᡼�����Ǻ����Ԥ�̾�������Ĥ���ޤ��� [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // �����Ԥ�̾������ (�������Ĥ���������)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // �����ԤΥ᡼�륢�ɥ쥹�μ����ȥ᡼������
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                ////// �����Υ᡼�륢�ɥ쥹����
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "���ΰ���� {$sponsor_name} ���������˥᡼������Ф�����ˤ��������������줿��ΤǤ���\n\n";
                $message .= "{$subject}\n\n";
                if ($cancel) {
                    $message .= "��������Ŭ������{$this->getUserName()}����ˤ��������ޤ����Τǡ���Ϣ���פ��ޤ���\n\n";
                } else {
                    $message .= "��������Ŭ�������������ޤ����Τǡ�����ǧ���ꤤ�פ��ޤ���\n\n";
                }
                $message .= "                               ��\n\n";
                $message .= "��. ȯ����    ��{$year}ǯ {$month}�� {$day}��({$dayWeek})\n\n";
                $message .= "��. ��Ŭ�����ơ�{$subject}\n\n";
                $message .= "��. ȯ�����  ��{$place}\n\n";
                $message .= "��. ��Ǥ����  ��{$section}\n\n";
                $message .= "��. ������    ��{$sponsor_name}\n\n";
                $message .= "��. �����    ��{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "�ʾ塢���������ꤤ�פ��ޤ���\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '���衧 ' . $atten_name[$i] . ' �͡� ' . $subject;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // �����ؤΥ᡼�������������¸
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
                ////// Debug
                if ($cancel) {
                    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                }
            }
            return true;
        }
        return false;
    }
    ////////// �ե������åפΰ���� email �ǽФ���
    protected function guideFollowMail($request, $serial_no)
    {
        ////// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');            // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');              // ȯ��ǯ������������
        $subject    = $request->get('subject');             // ��Ŭ������
        $sponsor    = $request->get('sponsor');             // �ե������å׺�����
        $atten      = $request->get('atten');               // �����(attendance) (����)
        $place      = $request->get('place');               // ȯ�����
        $section    = $request->get('section');             // ��Ǥ����
        $atten_num  = count($atten);                        // ������
        $mail       = $request->get('mail');                // �᡼������� Y/N
        ////// �������������
        $week = array('��', '��', '��', '��', '��', '��', '��');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        ////// �����Ԥ�̾�������
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "�᡼�����Ǻ����Ԥ�̾�������Ĥ���ޤ��� [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // �����Ԥ�̾������ (�������Ĥ���������)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // �����ԤΥ᡼�륢�ɥ쥹�μ����ȥ᡼������
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                ////// �����Υ᡼�륢�ɥ쥹����
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "���ΰ���� {$sponsor_name} ���������˥᡼������Ф�����ˤ��������������줿��ΤǤ���\n\n";
                $message .= "{$subject}\n\n";
                $message .= "��������Ŭ������Υե������åפ�������ޤ����Τǡ�����ǧ���ꤤ�פ��ޤ���\n\n";
                $message .= "                               ��\n\n";
                $message .= "��. ȯ����    ��{$year}ǯ {$month}�� {$day}��({$dayWeek})\n\n";
                $message .= "��. ��Ŭ�����ơ�{$subject}\n\n";
                $message .= "��. ȯ�����  ��{$place}\n\n";
                $message .= "��. ��Ǥ����  ��{$section}\n\n";
                $message .= "��. ������    ��{$sponsor_name}\n\n";
                $message .= "��. �����    ��{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "�ʾ塢���������ꤤ�פ��ޤ���\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '���衧 ' . $atten_name[$i] . ' �͡� ' . $subject;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // �����ؤΥ᡼�������������¸
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
            }
            return true;
        }
        return false;
    }
    ////////// ����襰�롼�פ���Ͽ (�¹���)
    protected function groupInsert($group_no, $group_name, $atten, $owner)
    {
        ////// ������ last_date last_host ����Ͽ�����������
        ////// regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $insert_sql = '';
        $cnt = count($atten);
        for ($i=0; $i<$cnt; $i++) {
            $insert_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ����襰�롼�פ��ѹ� (�¹���)
    protected function groupUpdate($group_no, $group_name, $atten, $owner)
    {
        ////// ������ last_date last_host ����Ͽ�����������
        ////// regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        ////// ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $update_sql = '';
        $update_sql .= "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
            ;
        "; 
        $cnt = count($atten);
        ////// ͭ����̵���� active ���ѹ����� ���ͭ���Ȥʤ�
        for ($i=0; $i<$cnt; $i++) {
            $update_sql .= "
                INSERT INTO meeting_mail_group
                (group_no, group_name, atten, owner, active, last_date, last_host)
                VALUES
                ('$group_no', '$group_name', '{$atten[$i]}', '$owner', TRUE, '$last_date', '$last_host')
                ;
            ";
        }
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// ����襰�롼�פκ�� (�¹���)
    protected function groupDelete($group_no)
    {
        ////// ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        ////// �����SQLʸ������
        $delete_sql = "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// ��Ŭ������μ¹��� �ɲ�
    private function add_execute($request)
    {
        ////// �ѥ�᡼������ʬ��
        ////// unfit_report_header ����
        $year       = $request->get('yearReg');            // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');           // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');             // ȯ��ǯ������������
        $subject    = $request->get('subject');            // ��Ŭ������
        $place      = $request->get('place');              // ȯ�����
        $section    = $request->get('section');            // ��Ǥ����
        $sponsor    = $request->get('sponsor');            // ������
        $receipt_no = $request->get('receipt_no');         // ����No.
        ////// unfit_report_attendance ����
        $atten = $request->get('atten');                // �����(attendance) (����)
        $mail  = $request->get('mail');                 // �᡼������� Y/N
        ////// unfit_report_cause ����
        $assy_no     = $request->get('assy_no');        // �����ֹ�
        $parts_no    = $request->get('parts_no');       // �����ֹ�
        $occur_cause = $request->get('occur_cause');    // ȯ������
        $unfit_num   = $request->get('unfit_num');      // ��Ŭ�����
        $issue_cause = $request->get('issue_cause');    // ή�и���
        $issue_num   = $request->get('issue_num');      // ή�п���
        ////// unfit_report_measure ����
        $unfit_dispose      = $request->get('unfit_dispose');     // ��Ŭ���ʤν���
        $occur_measure      = $request->get('occur_measure');     // ȯ�����к�
        $occur_year         = $request->get('occur_yearReg');     // ȯ�����к��»�ͽ��ǯ����
        $occur_month        = $request->get('occur_monthReg');    // ȯ�����к��»�ͽ����
        $occur_day          = $request->get('occur_dayReg');      // ȯ�����к��»�ͽ��������
        $issue_measure      = $request->get('issue_measure');     // ή���к�
        $issue_year         = $request->get('issue_yearReg');     // ή���к��»�ͽ��ǯ����
        $issue_month        = $request->get('issue_monthReg');    // ή���к��»�ͽ����
        $issue_day          = $request->get('issue_dayReg');      // ή���к��»�ͽ��������
        $follow_who         = $request->get('follow_who');        // �ե������å�ï
        $follow_year        = $request->get('follow_yearReg');    // �ե������å�ͽ��ǯ����
        $follow_month       = $request->get('follow_monthReg');   // �ե������å�ͽ����
        $follow_day         = $request->get('follow_dayReg');     // �ե������å�ͽ��������
        $follow_how         = $request->get('follow_how');        // �ե������åפɤΤ褦��
        $measure            = $request->get('measure');           // �к���λ Y/N
        ////// unfit_report_develop ����
        $suihei   = $request->get('suihei');            // �»ܹ��� ��ʿŸ��
        $kanai    = $request->get('kanai');             // �»ܹ��� ����Ÿ��
        $kagai    = $request->get('kagai');             // �»ܹ��� �ݳ�Ÿ��
        $hyoujyun = $request->get('hyoujyun');          // �»ܹ��� ɸ���Ÿ��
        $kyouiku  = $request->get('kyouiku');           // �»ܹ��� ����»�
        $system   = $request->get('system');            // �»ܹ��� �����ƥ�
        ////// �»ܹ��ܤ� boolean�����Ѵ�
        if ($suihei == 't') $suihei = 'TRUE'; else $suihei = 'FALSE';
        if ($kanai == 't') $kanai = 'TRUE'; else $kanai = 'FALSE';
        if ($kagai == 't') $kagai = 'TRUE'; else $kagai = 'FALSE';
        if ($hyoujyun == 't') $hyoujyun = 'TRUE'; else $hyoujyun = 'FALSE';
        if ($kyouiku == 't') $kyouiku = 'TRUE'; else $kyouiku = 'FALSE';
        if ($system == 't') $system = 'TRUE'; else $system = 'FALSE';
        ////// �᡼������ Y/N �� boolean�����Ѵ�
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// �к���λ Y/N �� boolean�����Ѵ�
        if ($measure == 't') $measure = 'TRUE'; else $measure = 'FALSE';
        ////// ������ last_date last_host ����Ͽ�����������
        ////// regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// �����οͿ������
        $atten_num = count($atten);
        $insert_qry = "
            INSERT INTO unfit_report_header
            (subject, occur_time, place, section, sponsor, atten_num, mail, last_date, last_host, receipt_no)
            VALUES
            ('$subject', '{$year}-{$month}-{$day}', '$place', '$section', '$sponsor', $atten_num, $mail, '$last_date', '$last_host', '$receipt_no')
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $insert_qry .= "
                INSERT INTO unfit_report_attendance
                (serial_no, atten, mail)
                VALUES
                ((SELECT max(serial_no) FROM unfit_report_header), '{$atten[$i]}', FALSE)
                ;
            ";
        }
        $insert_qry .= "
            INSERT INTO unfit_report_cause
            (serial_no, assy_no, parts_no, occur_cause, unfit_num, issue_cause, issue_num)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), '$assy_no', '$parts_no', '$occur_cause', $unfit_num, '$issue_cause', $issue_num)
            ;
        ";
        $insert_qry .= "
            INSERT INTO unfit_report_measure
            (serial_no, unfit_dispose, occur_measure, occurMeasure_date, issue_measure, issueMeasure_date, follow_who, follow_when, follow_how, measure)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), '$unfit_dispose', '$occur_measure', '{$occur_year}-{$occur_month}-{$occur_day}', '$issue_measure', '{$issue_year}-{$issue_month}-{$issue_day}', '$follow_who', '{$follow_year}-{$follow_month}-{$follow_day}', '$follow_how', $measure)
            ;
        ";
        $insert_qry .= "
            INSERT INTO unfit_report_develop
            (serial_no, suihei, kanai, kagai, hyoujyun, kyouiku, system)
            VALUES
            ((SELECT max(serial_no) FROM unfit_report_header), $suihei, $kanai, $kagai, $hyoujyun, $kyouiku, $system)
            ;
        ";
        $insert_qry .= "
                INSERT INTO unfit_report_follow
                (serial_no, follow)
                VALUES
                ((SELECT max(serial_no) FROM unfit_report_header), 'f')
                ;
            ";
        if ($this->execute_Insert($insert_qry)) {
            $query = "SELECT max(serial_no) FROM unfit_report_header";
            $serial_no = false;                                 // �����
            $this->getUniResult($query, $serial_no);
            return $serial_no;                                  // ��Ͽ�������ꥢ���ֹ���֤�
        } else {
            return false;
        }
    }
    
    ////////// ��Ŭ������μ¹��� �ե������å�
    private function follow_execute($request)
    {
        ////// �ѥ�᡼������ʬ��
        $serial_no      = $request->get('serial_no');           // Ϣ��(�����ե������)
        $follow_section = $request->get('follow_section');      // �ե������å� ȯ������
        $follow_quality = $request->get('follow_quality');      // �ե������å� �ʼ��ݾڲ�
        $follow_opinion = $request->get('follow_opinion');      // �ե������å� �ո�
        $follow         = $request->get('follow');              // �ե������å״�λ Y/N
        $sponsor        = $request->get('sponsor');             // ������
        ////// unfit_report_attendance ����
        $atten = $request->get('atten');                        // �����(attendance) (����)
        $mail  = $request->get('mail');                         // �᡼������� Y/N
        ////// �᡼������ Y/N �� boolean�����Ѵ�
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// �ե������å״�λ Y/N �� boolean�����Ѵ�
        if ($follow == 't') $follow = 'TRUE'; else $follow = 'FALSE';
        ////// ������ last_date last_host ����Ͽ�����������
        ////// ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        ////// regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// �����οͿ������
        $atten_num = count($atten);
        $chk_sql = "
            SELECT * FROM unfit_report_follow WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {        // ����Υ��ꥢ���ֹ��¸�ߥ����å�
            $insert_qry = "
                DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
                ;
            ";
        
            for ($i=0; $i<$atten_num; $i++) {
                $insert_qry .= "
                    INSERT INTO unfit_report_attendance
                    (serial_no, atten, mail)
                    VALUES
                    ({$serial_no}, '{$atten[$i]}', FALSE)
                    ;
                ";
            }
            $insert_qry .= "
                INSERT INTO unfit_report_follow
                (serial_no, follow_section, follow_quality, follow_opinion, follow_sponsor, follow, follow_date, last_host)
                VALUES
                ($serial_no, '$follow_section', '$follow_quality', '$follow_opinion', '$sponsor', $follow, '$last_date', '$last_host')
                ;
            ";
            if ($this->execute_Insert($insert_qry)) {
                return $serial_no;                              // ��Ͽ�������ꥢ���ֹ���֤�
            } else {
                return false;
            }
        } else {
            $update_sql = "
                UPDATE unfit_report_follow SET
                follow_section='$follow_section', follow_quality='$follow_quality', follow_opinion='$follow_opinion',
                follow_sponsor='$sponsor', follow='$follow',follow_date='{$last_date}', last_host='{$last_host}'
                where serial_no={$serial_no}
            ;
            ";
            $update_sql .= "
                DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
                ;
            ";
        
            for ($i=0; $i<$atten_num; $i++) {
                $update_sql .= "
                    INSERT INTO unfit_report_attendance
                    (serial_no, atten, mail)
                    VALUES
                    ({$serial_no}, '{$atten[$i]}', FALSE)
                    ;
                ";
            }
            if ($this->execute_Update($update_sql, $save_sql)) {
                return $serial_no;                              // ��Ͽ�������ꥢ���ֹ���֤�
            } else {
                return false;
            }
        }
        
    }
    
    ////////// ��Ŭ������μ¹��� ���(����)
    private function del_execute($serial_no, $subject)
    {
        ////// ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        $delete_sql = "
            DELETE FROM unfit_report_header WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_cause WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_measure WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_develop WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
            ;
        ";
        ////// ��Ŭ������κ����Ʊ���˥ե������åפ�������
        $delete_sql .= "
            DELETE FROM unfit_report_follow WHERE serial_no={$serial_no}
            ;
        ";
        ////// $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ��Ŭ������μ¹��� �ѹ�
    private function edit_execute($request)
    {
        ////// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');               // Ϣ��(�����ե������)
        ////// unfit_report_header ����
        $year       = $request->get('yearReg');                 // ȯ��ǯ������ǯ����
        $month      = $request->get('monthReg');                // ȯ��ǯ�����η��
        $day        = $request->get('dayReg');                  // ȯ��ǯ������������
        $subject    = $request->get('subject');                 // ��Ŭ������
        $place      = $request->get('place');                   // ȯ�����
        $section    = $request->get('section');                 // ��Ǥ����
        $sponsor    = $request->get('sponsor');                 // ������
        $receipt_no = $request->get('receipt_no');              // ����No.
        ////// unfit_report_attendance ����
        $atten = $request->get('atten');                        // �����(attendance) (����)
        $mail  = $request->get('mail');                         // �᡼������� Y/N
        ////// unfit_report_cause ����
        $assy_no     = $request->get('assy_no');                // �����ֹ�
        $parts_no    = $request->get('parts_no');               // �����ֹ�
        $occur_cause = $request->get('occur_cause');            // ȯ������
        $unfit_num   = $request->get('unfit_num');              // ��Ŭ�����
        $issue_cause = $request->get('issue_cause');            // ή�и���
        $issue_num   = $request->get('issue_num');              // ή�п���
        ////// unfit_report_measure ����
        $unfit_dispose      = $request->get('unfit_dispose');   // ��Ŭ���ʤν���
        $occur_measure      = $request->get('occur_measure');   // ȯ�����к�
        $occur_year         = $request->get('occur_yearReg');   // ȯ�����к��»�ͽ��ǯ����
        $occur_month        = $request->get('occur_monthReg');  // ȯ�����к��»�ͽ����
        $occur_day          = $request->get('occur_dayReg');    // ȯ�����к��»�ͽ��������
        $issue_measure      = $request->get('issue_measure');   // ή���к�
        $issue_year         = $request->get('issue_yearReg');   // ή���к��»�ͽ��ǯ����
        $issue_month        = $request->get('issue_monthReg');  // ή���к��»�ͽ����
        $issue_day          = $request->get('issue_dayReg');    // ή���к��»�ͽ��������
        $follow_who         = $request->get('follow_who');      // �ե������å�ï
        $follow_year        = $request->get('follow_yearReg');  // �ե������å�ͽ��ǯ����
        $follow_month       = $request->get('follow_monthReg'); // �ե������å�ͽ����
        $follow_day         = $request->get('follow_dayReg');   // �ե������å�ͽ��������
        $follow_how         = $request->get('follow_how');      // �ե������åפɤΤ褦��
        $measure            = $request->get('measure');         // �к���λ Y/N
        ////// unfit_report_develop ����
        $suihei   = $request->get('suihei');                    // �»ܹ��� ��ʿŸ��
        $kanai    = $request->get('kanai');                     // �»ܹ��� ����Ÿ��
        $kagai    = $request->get('kagai');                     // �»ܹ��� �ݳ�Ÿ��
        $hyoujyun = $request->get('hyoujyun');                  // �»ܹ��� ɸ���Ÿ��
        $kyouiku  = $request->get('kyouiku');                   // �»ܹ��� ����»�
        $system   = $request->get('system');                    // �»ܹ��� �����ƥ�
        if ($suihei == 't') $suihei = 'TRUE'; else $suihei = 'FALSE';
        if ($kanai == 't') $kanai = 'TRUE'; else $kanai = 'FALSE';
        if ($kagai == 't') $kagai = 'TRUE'; else $kagai = 'FALSE';
        if ($hyoujyun == 't') $hyoujyun = 'TRUE'; else $hyoujyun = 'FALSE';
        if ($kyouiku == 't') $kyouiku = 'TRUE'; else $kyouiku = 'FALSE';
        if ($system == 't') $system = 'TRUE'; else $system = 'FALSE';
        ////// �᡼������ Y/N �� boolean�����Ѵ�
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        ////// �к���λ Y/N �� boolean�����Ѵ�
        if ($measure == 't') $measure = 'TRUE'; else $measure = 'FALSE';
        ////// ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM unfit_report_header WHERE serial_no={$serial_no}
        ";
        ////// ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        ////// �����οͿ������
        $atten_num = count($atten);
        $update_sql = "
            UPDATE unfit_report_header SET
            subject='{$subject}', occur_time='{$year}-{$month}-{$day}', place='$place',
            section='$section', sponsor='{$sponsor}', atten_num='{$atten_num}', mail='$mail',
            last_date='{$last_date}', last_host='{$last_host}', receipt_no='{$receipt_no}'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_cause SET
            assy_no='{$assy_no}', parts_no='{$parts_no}', occur_cause='{$occur_cause}',
            unfit_num='$unfit_num', issue_cause='{$issue_cause}', issue_num='$issue_num'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_measure SET
            unfit_dispose='{$unfit_dispose}', occur_measure='{$occur_measure}', occurMeasure_date='{$occur_year}-{$occur_month}-{$occur_day}',
            issue_measure='{$issue_measure}', issueMeasure_date='{$issue_year}-{$issue_month}-{$issue_day}', follow_who='{$follow_who}',
            follow_when='{$follow_year}-{$follow_month}-{$follow_day}', follow_how='{$follow_how}', measure='{$measure}'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            UPDATE unfit_report_develop SET
            suihei='$suihei', kanai='$kanai', kagai='$kagai',
            hyoujyun='$hyoujyun', kyouiku='$kyouiku', system='$system'
            where serial_no={$serial_no}
            ;
        ";
        $update_sql .= "
            DELETE FROM unfit_report_attendance WHERE serial_no={$serial_no}
            ;
        ";
        
        for ($i=0; $i<$atten_num; $i++) {
            $update_sql .= "
                INSERT INTO unfit_report_attendance
                (serial_no, atten, mail)
                VALUES
                ({$serial_no}, '{$atten[$i]}', FALSE)
                ;
            ";
        }
        ////// $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// �����Ԥ�̾�������
    private function getSponsorName($sponsor, &$res)
    {
        $query = "
            SELECT trim(name), trim(mailaddr)
            FROM
                user_detailes
            LEFT OUTER JOIN
                user_master USING(uid)
            WHERE
                uid = '{$sponsor}'
                AND
                retire_date IS NULL     -- �࿦���Ƥ��ʤ�
                AND
                sid != 31               -- �и����Ƥ��ʤ�
        ";
        $res = array();                                         // �����
        if ($this->getResult2($query, $res) < 1) {
            return false;
        } else {
            return true;
        }
    }
    
    ////////// ������̾������
    private function getAttendanceName($atten, &$atten_name, &$flag)
    {
        $atten_num = count($atten);
        $atten_name = array();
        $flag = array();
        for ($i=0; $i<$atten_num; $i++) {
            $query = "
                SELECT trim(name) FROM user_detailes WHERE uid = '{$atten[$i]}' AND retire_date IS NULL AND sid != 31
            ";
            $atten_name[$i] = '';
            if ($this->getUniResult($query, $atten_name[$i]) < 1) {
                $_SESSION['s_sysmsg'] .= "�᡼������������̾�������Ĥ���ޤ��� [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// �����Υ᡼�륢�ɥ쥹����
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "�᡼�����������Υ᡼�륢�ɥ쥹�����Ĥ���ޤ��� [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// ������̾����᡼��˺ܤ��뤿��ʸ����ǰ�����
    private function getAttendanceNameList($atten, $atten_name)
    {
        $atten_num = count($atten);
        $message = '';
        for ($j=0; $j<$atten_num; $j++) {
            if (!$atten_name[$j]) continue;
            if ($j == 0) {
                $message .= "{$atten_name[$j]}";
            } else {
                $message .= ", {$atten_name[$j]}";
            }
        }
        return $message;
    }
    
    ////////// �����ؤΥ᡼�������������¸
    private function setAttendanceMailHistory($serial_no, $atten)
    {
        $update_sql = "
            UPDATE meeting_schedule_attendance SET
                mail=TRUE
            WHERE
                serial_no={$serial_no} AND atten='{$atten}'
        ";
        $this->execute_Update($update_sql);
    }
    
    ////////// ���饤����Ȥ�̾������
    private function getUserName()
    {
        if (!$_SESSION['User_ID']) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        $query = "
            SELECT trim(name) FROM user_detailes WHERE uid = '{$_SESSION['User_ID']}' AND retire_date IS NULL AND sid != 31
        ";
        if ($this->getUniResult($query, $userName) < 1) {
            return gethostbyaddr($_SERVER['REMOTE_ADDR']);
        } else {
            return $userName;
        }
    }
    
} //////////// Class UnfitReport_Model End

?>
