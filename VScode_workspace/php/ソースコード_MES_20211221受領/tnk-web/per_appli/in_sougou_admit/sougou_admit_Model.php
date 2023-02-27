<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                                                              MVC Model ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_Model.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class
require_once ('../../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class


/******************************************************************************
*          ����ϡʾ�ǧ���� MVC��Model�� base class ���쥯�饹�����          *
******************************************************************************/
class Sougou_Admit_Model extends ComTableMnt
{
    ///// Private properties
    private $admit;
    private $uid;
    private $mail;
    private $indx;
    private $rows;
    private $last_rows;
    private $res;
    private $yukyu = array(array(0,0,0,0,0));

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $uid='')
    {
        $this->last_rows = $request->get('rows');
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        if ($uid == '') {
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            $uid = sprintf('%06d', $uid);
            if( $uid == 0 ) return;
            $this->uid = $uid;    // Properties�ؤ���Ͽ
        }
        $sql_sum = "
            SELECT count(*) FROM sougou_deteils where admit_status like '%{$uid}'
        ";

        $query = "
            SELECT      admit_status
            FROM        sougou_deteils
            WHERE       admit_status = '$uid'
        ";
        $res = array();
        if ( ($this->admit = $this->getResult2($query, $res)) <= 0 ) {
            $this->admit = 0;
        }

        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'sougou_admit.log');
    }

    // ��ǧ���̤�ɽ����������Ϥ�����
    public function IsAdmit()
    {
        return $this->admit;
    }

    // ��Ĺ����Ĺ���� �Ǥ�����
    public function IsKatyou($uid)
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$uid' AND (ud.pid=46 OR ud.pid=50 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // �����Ǥ�����
    public function IsHoliday($date)
    {
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

        return true;
    }

    // �ƿ����Ǥ�����
    public function IsReAppl($date, $uid)
    {
        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   uid='{$uid}' AND re_date='{$date}';
            ";
        if( getResult2($query, $res) <= 0 || $res[0][2]!='t') {
            return false;
        }
        return true;
    }

    // ��������������
    public function GetPreviousDate($date, $uid)
    {
        $query = "
                SELECT  *
                FROM    sougou_reappl
                WHERE   uid='{$uid}' AND re_date='{$date}';
            ";
        if( getResult2($query, $res) <= 0 || $res[0][2]!='t') {
            return "";
        }
        return $res[0][0];
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

    // ��������ǧ ������ �ƥ���
    public function getAgentList()
    {
        if( $this->IsKatyou($this->uid) ) {
            $query = "
                SELECT          DISTINCT kakarityo
                FROM            approval_path
                WHERE           kakarityo!='------' AND katyo='$this->uid'
            ";
        } else if( $this->IsButyou($this->uid) ) {
            $query = "
                SELECT          DISTINCT katyo
                FROM            approval_path
                WHERE           katyo!='------' AND butyo='$this->uid'
            ";
        } else {
            return '';
        }
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return '';
        }

        return $res;
    }

    // ��ǧ��UID����
    public function getAdmitUID(&$res)
    {
        $query = "
            SELECT DISTINCT admit_status
            FROM            sougou_deteils
            WHERE           admit_status!='END' AND admit_status!='DENY' AND admit_status!='CANCEL'
        ";
        $res = array();
        return getResult2($query, $res);
    }

    // ��ǧ�Ԥ��������
    public function getAdmitCnt($uid)
    {
        $query = "SELECT count(*) FROM sougou_deteils where admit_status='$uid'";
        $res = array();
        if( getResult2($query, $res) <= 0 ) return 0;
        
        return $res[0][0];
    }

    // ��ǧ�����᡼������
    public function AdmitRequestMaile($send_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
        $search_m = "WHERE uid='$send_uid'";
        $search_m = "WHERE uid='300667'";   //  TEST ����Ū���Ѵ�����ʬ�˥᡼�������
        $query_m = sprintf("$query_m %s", $search_m);     // SQL query ʸ�δ���
        $res_sum_m = array();
        if ($this->getResult2($query_m, $res_sum_m) <= 0) {
            exit();
        } else {
            $sendna = $res_sum_m[0][0];
            $sendna = trim($this->getSyainName($send_uid));    // TEST ����Ū���Ѵ�
            $mailad = $res_sum_m[0][1];
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
            $attenSubject = "���衧 {$sendna} �� ����ϡھ�ǧ�����ۤΤ��Τ餻";
            $request_name = trim($this->getSyainName($this->uid));
            $message  = "{$sendna} ��\n\n";
            $message .= "{$request_name} �� ��� ��ǧ�Ԥ�����Ϥ��������褦����������ޤ�����\n\n";
            $message .= "��ޤˡ���ǧ�����򤹤�褦���ꤤ���ޤ���\n\n";
            $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid={$send_uid}";
            echo $message;
//            mb_send_mail($to_addres, $attenSubject, $message, $add_head);
        }
    }

    // ��Ĺ����Ĺ���� �Ǥ����� 95=������Ĺ
    public function IsButyou($uid)
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // ����Ĺ �Ǥ�����
    public function IsKoujyoutyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->uid' AND ud.pid=110
        ";
        $res = array();

        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return false;
        }

        return true;
    }

    // �����󤷤Ƥ���桼����ID
    public function getUid()
    {
        return $this->uid;
    }

    // ��ǧ���̤�ɽ�����������DB���
    public function getIndx()
    {
        return $this->indx;
    }

    // ��ǧ���̤�ɽ�����������DB�Կ�
    public function getRows()
    {
        return $this->rows;
    }

    // ��ǧ���̤�ɽ�����������DB
    public function getRes()
    {
        return $this->res;
    }

    // ��ǧ��ϩ����
    public function ApprovalPathUpdate($no, $admit_date, $date, $uid )
    {
        switch ($no) {
        case 31:
            $update_qry = sprintf("UPDATE approval_path SET kakarityo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 33:
            $update_qry = sprintf("UPDATE approval_path SET katyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 35:
            $update_qry = sprintf("UPDATE approval_path SET butyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 37:
            $update_qry = sprintf("UPDATE approval_path SET somukatyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 39:
            $update_qry = sprintf("UPDATE approval_path SET kanributyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        case 41:
            $update_qry = sprintf("UPDATE approval_path SET kojyotyo_date='%s' WHERE date='%s' AND uid='%s'", $admit_date, $date, $uid);
            break;
        }

//      $_SESSION['s_sysmsg'] .= $update_qry;
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "��ǧ�ι����˼��Ԥ��ޤ�����";
        }
    }

    // ���ż���Ͽ
    public function JyudenUpdate($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // ���ϲ��⤷�ʤ���

        // ����Ϥ����ʬ�롼��
        for( $r=0; $r<$last_rows; $r++ ) {
            $state = $request->get('jyu_register' . $r);
            if( $state != 'ok' ) continue;  // ���ż���Ͽ�ʳ������Ф���

            // ���ż���Ͽ��ɬ�פʥǡ����������
            $res = $request->get(sprintf("res-%s", $r));
            $date                   = $res[0];                          // ����ǯ����
            $uid                    = $res[1];                          // ������ �Ұ��ֹ�

            $received_phone_date    = $request->get('jyu_date' . $r);   // ���żԡ�������
            $received_phone_name    = $request->get('outai' . $r);      // ���żԡʱ��мԡ�
            if( is_numeric(trim($received_phone_name)) ) {
                $received_phone_name = $this->getSyainName($received_phone_name);
            }

            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='���ż�', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone_date, $received_phone_name, $date, $uid);
//            $_SESSION['s_sysmsg'] .= "{$update_qry}";
            if( query_affected($update_qry) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "���żԤ���Ͽ�˼��Ԥ��ޤ�����";
            }
        }
    }

    // ��ǧ���֤ι�������ǧ���᡼������
    public function admitUpdate($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // ���ϲ��⤷�ʤ���

        $next_mail = array();
        // ���������
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            $state = $request->get(15000+$r);       // ��ǧ or ��ǧ
            if( $state != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }

                $date = $request->get(20000+$r);    // ����
                // ��ǧ��ϩ�ơ��֥빹��
                for( $i=31; $i<$last_indx; $i++ ) {
                    if( !strstr($last_res[$r][$i], $this->uid) ) continue;

                    $this->ApprovalPathUpdate($i, $date, $last_res[$r][0], $last_res[$r][1] );
                    $last_res[$r][$i+1] = $date;    // ̾������ǧ�������å�������١�������Ȥ���

                    if( $state == '��ǧ' ) {
                        $next_admit = 'END';    // ���ξ�ǧ�ԥ����ɤ򥻥å�
                        // �����Ԥ���Ĺ��������Ĺ����Ĺ��������Ĺ �ΤȤ� ��ǧ�Ԥ� ����Ĺ �ʤ�
                        if( ($this->IsKatyou( $last_res[$r][1] ) || $this->IsButyou($last_res[$r][1])) && $this->IsKoujyoutyou() ) {
                            $i = 31; // ��ǧ�ԥơ��֥����Ƭ���鸡���Ƴ�
                        }

                        // ���ξ�ǧ�Ԥ�õ����
                        for( $n=$i+2; $n<$last_indx; $n=$n+2 ) {
                            $next_admit = 'END';    // ���ξ�ǧ�ԥ����ɤ򥻥å�
                            // ��ǧ�ԥ����ɤ��ꡦ��ǧ���ʤ��οͤ򼡤ξ�ǧ�Ԥإ��å�
                            if( is_numeric(($last_res[$r][$n])) && $last_res[$r][$n+1] == "" ) {
                                // ������Ĺ��ǧ���������Ԥ�������Ĺ�ʤ顢��ǧ�����å�
                                if( $last_res[$r][$n] == $last_res[$r][1] ) {
                                    $this->ApprovalPathUpdate($n, $date, $last_res[$r][0], $last_res[$r][1] );
                                    continue;
                                }

                                $next_admit = $last_res[$r][$n];    // ���ξ�ǧ��
                                $next_no = $n;                      // ���ξ��
                                // ���˾�ǧ���Ƥ���ʤ龵ǧ�����å�
                                for( $x=33; $x<$next_no+1; $x=$x+2 ) {
                                    if( $next_admit == $last_res[$r][$x] && $last_res[$r][$x+1] != ""  ) {
                                        $this->ApprovalPathUpdate($next_no, $date, $last_res[$r][0], $last_res[$r][1] );
                                        break;
                                    }
                                }
                                if( $x>$next_no+1) break;
                            }
                        }

                        // �����Ԥ���Ĺ����Ĺ���� �ΤȤ� ����Ĺ ̤��ǧ�ʤ�
                        if( $this->IsKatyou($last_res[$r][1]) && $last_res[$r][42] == "" ) {
                            $next_admit = $last_res[$r][41];    // ���ξ�ǧ�Ԥ򹩾�Ĺ��
                        }
                    } else {
                        $next_admit = 'DENY'; // ��ǧ
                        $reason = $request->get(10000+$r);  // ��ͳ

                        // ���򤵤줿�ͤء���ǧ�᡼�������
                        if( $request->get(70000+$r."_sinsei") ) {
                            $this->DenyMaile(trim($last_res[$r][1]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kakari") ) {
                            $this->DenyMaile(trim($last_res[$r][31]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_katyo") ) {
                            $this->DenyMaile(trim($last_res[$r][33]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_butyo") ) {
                            $this->DenyMaile(trim($last_res[$r][35]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_soumu") ) {
                            $this->DenyMaile(trim($last_res[$r][37]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kanri") ) {
                            $this->DenyMaile(trim($last_res[$r][39]), $last_res, $r, $reason);
                        }
                        if( $request->get(70000+$r."_kojyo") ) {
                            $this->DenyMaile(trim($last_res[$r][41]), $last_res, $r, $reason);
                        }

                        $insert_qry = "
                            INSERT INTO admit_stop_reason (date, uid, state, reason) VALUES
                                ('{$last_res[$r][0]}', '{$last_res[$r][1]}', '$next_admit', '$reason');
                        ";
                        if( query_affected($insert_qry) <= 0 ) {
                            $_SESSION['s_sysmsg'] .= "��ǧ��ͳ����Ͽ�˼��Ԥ��ޤ�����";
                        }
                    }

                    $update_qry = sprintf("UPDATE sougou_deteils SET admit_status = '%s' WHERE date='%s' AND uid='%s'", $next_admit, $last_res[$r][0], $last_res[$r][1]);
//                    $_SESSION['s_sysmsg'] .= $update_qry;

                    if( query_affected($update_qry) <= 0 ) {
                        $_SESSION['s_sysmsg'] .= "���ξ�ǧ�Ԥع����˼��Ԥ��ޤ�����";
                    }

                    if( is_numeric($next_admit) && $last_res[$r][24] == "���" ) {
                        $this->hurryMaile( $next_admit );
                    }
                    break;
                } // ��ǧ��ϩ�ơ��֥빹��

                // ���ξ�ǧ�Ԥ����Τ������
                if( $request->get('next') != '' && is_numeric($next_admit) ) {
                    if( ! in_array($next_admit, $next_mail) ) {
                        array_push( $next_mail, $next_admit );
                    }
                }
            }
        }
        // ���ξ�ǧ�Ԥ����Τ������
        $max = count($next_mail);
        for( $r=0; $r<$max; $r++ ) {
//            $_SESSION['s_sysmsg'] .= "[" . $next_mail[$r] . "]";
            $this->nextMaile( $next_admit );
        }

        return ;
    }

    // ��ǧ���̤�ɽ�����������DB����
    public function getViewDataList(&$result)
    {
        ///// ��� $partsKey �ե�����ɤǤθ���
        $query = "
            SELECT      *
            FROM        sougou_deteils AS sd
            LEFT JOIN   approval_path AS ap
            ON          sd.uid = ap.uid AND sd.date = ap.date
            WHERE       sd.admit_status = '{$this->uid}'
            ORDER BY    sd.hurry ASC, sd.date ASC, ap.uid ASC
        ";
        $res = $field = array();
        $rows = getResultWithField2( $query, $field, $res );
        if ( $rows <= 0 ) {
            $this->admit = 0;
//            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
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

    // �Ұ���̾�������
    public function getSyainName($uid)
    {
        if( $uid == '------' ) return $uid;
        $uid = sprintf('%06d', $uid);
        $query = "
            SELECT      name
            FROM        user_detailes
            WHERE       uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return $uid;
        }
        return $res[0][0];
    }

    // �������줿����Ϥ��Ф��뾵ǧ�롼�Ȥ����
    public function getAdmit(&$request, $date, $uid)
    {
        $query = "
            SELECT      *
            FROM        approval_path
            WHERE       date = '$date' AND uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        $request->add("kakarityo", $res[0][3]);
        if($res[0][4]=='') $res[0][4] = "------";
        $request->add("kakarityo_date", $res[0][4]);
        $request->add("katyo", $res[0][5]);
        if($res[0][6]=='') $res[0][6] = "------";
        $request->add("katyo_date", $res[0][6]);
        $request->add("butyo", $res[0][7]);
        if($res[0][8]=='') $res[0][8] = "------";
        $request->add("butyo_date", $res[0][8]);
        $request->add("somukatyo", $res[0][9]);
        if($res[0][10]=='') $res[0][10] = "------";
        $request->add("somukatyo_date", $res[0][10]);
        $request->add("kanributyo", $res[0][11]);
        if($res[0][12]=='') $res[0][12] = "------";
        $request->add("kanributyo_date", $res[0][12]);
        $request->add("kojyotyo", $res[0][13]);
        if($res[0][14]=='') $res[0][14] = "------";
        $request->add("kojyotyo_date", $res[0][14]);
        return $res;
    }

    // ��޻��᡼������
    public function nextMaile($admit_uid)
    {
        $query_m = "SELECT trim(name), trim(mailaddr)
                        FROM
                            user_detailes
                        LEFT OUTER JOIN
                            user_master USING(uid)
                        ";

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
            // �����ȥ�
            $attenSubject = "���衧 {$sendna} �� ����ϡʾ�ǧ�ˤ�ꤪ�Τ餻";
            // ��å�����
            $name = trim($this->getSyainName($this->getUid()));
            $message  = "{$name} �ͤ���ǧ��Ԥ��ޤ�����\n";
            $message .= "��ǧ�Ԥ�������Ϥ�����ޤ��Τ�\n";
            $message .= "��ǧ���Ƥ���������\n\n";
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
            //$message  = "{$admit_uid} �� ��ޤ�����Ϥ�����ޤ���\n\n";
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

    // ��ǧ���᡼������
    public function DenyMaile($send_uid, $res, $r, $reason)
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
        $search_m = "WHERE uid='$send_uid'";

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
            $attenSubject = "���衧 {$sendna} �� ����ϡ���ǧ�ۤΤ��Τ餻";
            $syozoku = trim($this->getSyozoku($res[$r][1]));
            $name = trim($this->getSyainName($res[$r][1]));
            $deny_name = trim($this->getSyainName($this->uid));
            //�ƥ����� ��å�����
            //$message  = "{$send_uid} �ͤ� {$deny_name} �ͤˤ�ꡢ�ʲ�������Ϥ���ǧ����ޤ�����\n\n";
            //�ƥ����� �����ѹ����뤳��
            $message  = "*** {$name} �� ����ͭ�᡼��ξ�硢��Ĺ��ꤪ�Τ餻��������***\n\n";
            $message .= "{$deny_name} �ͤˤ�ꡢ�ʲ�������Ϥ���ǧ����ޤ�����\n\n";
            $message .= "{$syozoku} {$name}\n";         // ��° ��̾
            $message .= "{$res[$r][2]}";                // ������
            if( $res[$r][2] != $res[$r][4] ) {
                $message .= " �� {$res[$r][4]}";        // ��������
            }
            $message .= "\n{$res[$r][6]}\n\n";          // ��������
            $message .= "����ǧ��ͳ��{$reason}\n\n";    // ��ǧ��ͳ
            $res[$r][0] = str_replace(' ','@', $res[$r][0]);
            if( $send_uid == $res[$r][1] ) {
                $message .= "�ƿ����ϡ������� ������ ";
                $message .= "http://masterst/per_appli/in_sougou/sougou_Main.php?calUid={$send_uid}&showMenu=Re&date={$res[$r][0]}&syainbangou={$send_uid}&deny_uid={$this->uid}\n\n";
                $message .= "�����μ�äϡ������� �� ";
                $message .= "http://masterst/per_appli/in_sougou/sougou_Main.php?calUid={$send_uid}&showMenu=Del&date={$res[$r][0]}&syainbangou={$send_uid}&deny_uid={$this->uid}\n\n";
            }
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

    // ͭ�ټ������ʼ��ӡ�
    public function KeikakuCnt()
    {
        $this->yukyu[0][0]; // ����ͭ������
        $this->yukyu[0][1]; // ����ͭ���
        $this->yukyu[0][2]; // Ⱦ��ͭ����
        $this->yukyu[0][3]; // ���ֵټ���ʬ
        $this->yukyu[0][4]; // ����ͭ�����
        
        if( $this->yukyu[0][3] == 0 ) {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] );
        }else {
            $jisseki = $this->yukyu[0][0] - ($this->yukyu[0][1] + (round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3)) );
        }

        return $jisseki;
    }

    // ͽ��ͭ��ʾ�ǧ�ѡ�
    public function YoteiKyuka($uid, $sin_date, $start_date, $end_date, $half)
    {
        if( $sin_date == "" ) return;

        $cnt = 0;
        if( $half ) {
            $s_day = substr($sin_date,0,4) . "-03-31";
            $e_day = substr($sin_date,0,4) . "-10-01";
        } else {
            $s_day = substr($sin_date,0,4)+1 . "-09-30";
            $e_day = substr($sin_date,0,4)+1 . "-04-01";
        }

        // ͽ��ͭ��������
        $query = "
                SELECT  start_date      AS ������,      -- 0
                        end_date        AS ��λ��,      -- 1
                        content         AS ����         -- 2
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND (start_date!='{$start_date}' AND end_date!='{$end_date}')
                    AND start_date>'{$s_day}' AND end_date<'{$e_day}'
                    AND uid='$uid'
                    AND (content='ͭ��ٲ�' OR content='AMȾ��ͭ��ٲ�' OR content='PMȾ��ͭ��ٲ�' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : ͽ��ʤ�';
            return 0;
        }

        // ͭ��Ĥ�ͽ��ͭ��βû�����
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

    // ͭ��ľ�������
    public function getYukyu()
    {
        return $this->yukyu;
    }

    // ͭ��ľ���׻��������Ұ���˥塼 view_mineinfo.php �ե������������
    public function setYukyu($uid)
    {
        $timeDate = date('Ym');
        $today_ym = date('Ymd');
        $tmp = $timeDate - 195603;     // ���׻�����195603
        $tmp = $tmp / 100;             // ǯ����ʬ����Ф�
        $ki  = ceil($tmp);             // roundup ��Ʊ��
        $query = "
                SELECT
                     current_day    AS ����ͭ������     -- 0
                    ,holiday_rest   AS ����ͭ���       -- 1
                    ,half_holiday   AS Ⱦ��ͭ����     -- 2
                    ,time_holiday   AS ���ֵټ���ʬ     -- 3
                    ,time_limit     AS ����ͭ�����     -- 4
                    ,web_ymd        AS ����ǯ����       -- 5
                FROM holiday_rest_master
                WHERE uid='{$uid}' and ki<={$ki}
                ORDER BY ki DESC LIMIT 1
            ";
        if( getResult2($query, $this->yukyu) <= 0 ) {
            $this->yukyu = array(array(0,0,0,0,0));
            return false;
        }
        return true;
    }

    // ͭ��Ĥ�ͽ��ͭ��û�
    public function setYotei($sin_date, $uid)
    {
        // ͽ��ͭ��������
        $query = "
                SELECT  start_date      AS ������,      -- 0
                        end_date        AS ��λ��,      -- 1
                        start_time      AS ���ϻ���,    -- 2
                        end_time        AS ��λ����,    -- 3
                        content         AS ����         -- 4
                FROM    sougou_deteils
                WHERE   (start_date>'{$sin_date}' OR end_date>'{$sin_date}')
                    AND uid='{$uid}'
                    AND (content='ͭ��ٲ�' OR content='AMȾ��ͭ��ٲ�' OR content='PMȾ��ͭ��ٲ�' OR content='����ñ��ͭ��ٲ�' )
                    AND (admit_status != 'CANCEL' AND admit_status != 'DENY')
            ";
        if( ($rows = getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= $sin_date . ' : ͽ��ʤ�';
            return;
        }

        // ͭ��Ĥ�ͽ��ͭ��βû�����
        $timecount = false;     // ���ֵ٥ե饰�����
        for( $i=0; $i<$rows; $i++ ) {
            if( strtotime($res[$i][0]) <= strtotime($sin_date) ) {  // �������ȿ����������
                // �������ȿ���������Ӥ��������������ξ�硣�������˿������μ������򥻥å�
                $res[$i][0] = date('Ymd', strtotime($sin_date . ' 1 day'));
            }
            $day = $this->getDayCount($res[$i][0], $res[$i][1]);    // �Ķ�����μ�������
            if( trim($res[$i][4]) == 'ͭ��ٲ�' ) {
                $this->yukyu[0][1] -= $day;         // ����ͭ���
            } else if( trim($res[$i][4]) == 'AMȾ��ͭ��ٲ�' || trim($res[$i][4]) == 'PMȾ��ͭ��ٲ�' ) {
                $this->yukyu[0][1] -= (0.5 * $day); // ����ͭ���
                $this->yukyu[0][2] += $day;         // Ⱦ��ͭ����
            } else if( trim($res[$i][4]) == '����ñ��ͭ��ٲ�' ) {
                if( !$timecount ) {
                    // ����ͭ��Ĥش��˼������Ƥ�����ֵ٤�û����롣
                    // �����Τޤ�ͭ��Ĥ�긺������Ȥ��������ͤˤʤäƤ��ޤ����ᡣ
                    $this->yukyu[0][1] += round($this->yukyu[0][3]/($this->yukyu[0][4]/5), 3);
                    $timecount = true;  // ���ֵ٥ե饰���å�
                }
                $this->yukyu[0][3] += ($this->getTimeCount($res[$i][2], $res[$i][3]) * $day);    // ���ֵٲû�
            }
        }
        if( $timecount ) {
            // ����ͭ��Ĥ����ֵٸ���
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

    // �Խ���������ϤΥǡ���������
    public function getEditData($request)
    {
        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // ���ϲ��⤷�ʤ���

        // ���������
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get(90000+$r) != '' ) {
                $request->add('showMenu', 'Edit');
                break;
            }
        }

        if( $r>=$last_rows ) return false;

        $res = $request->get(sprintf("res-%s", $r));
        for( $i=0; $i<$last_indx; $i++ ) {
            $last_res[0][$i] = $res[$i];
        }
        if( $this->setYukyu($last_res[0][1]) ) {
//            $this->setYotei($last_res[0][0], $last_res[0][1]);
        }
        $this->indx = $last_indx;
        $this->rows = $last_rows;
        $this->res = $last_res;
    }

    // ����Ϥο������Ƥ�DB����Ͽ
    public function SougouUpdate($request)
    {
        ///// �ѥ�᡼������ʬ��
        $date                   = $request->get("sin_date");        // ����ǯ����
        $uid                    = $request->get("syain_no");        // ������ �Ұ��ֹ�
        $start_date             = $request->get("str_date");        // ���� ���� ����
        $start_time             = $request->get("str_time");        // ���� ���� ����
        $end_date               = $request->get("end_date");        // ���� ��λ ����
        $end_time               = $request->get("end_time");        // ���� ��λ ����
        $content                = $request->get('r1');              // ���ơʥ饸��1��
        $yukyu                  = $request->get('r2');              // ���ơʥ饸��2��ͭ���Ϣ
        $ticket01               = $request->get('r3');              // ���ơʥ饸��3�˾�ַ�
        $ticket02               = $request->get('r4');              // ���ơʥ饸��4�˿�����
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
        if( is_numeric(trim($received_phone_name)) ) {
            $received_phone_name = $this->getSyainName($received_phone_name);
        }
        if( $received_phone_name) $received_phone = '���ż�';       // ���мԤ�������������å�����żԤˤ���

        $hurry                  = $request->get('c2');              // ��ޡʥ����å���

        if( $ticket01 == "��ƻ" || $ticket01 == "����" || $ticket02 == "��ƻ" || $ticket02 == "����" ) {
//        if( ($ticket01 != "" && $ticket01 != "����") || ($ticket02 != "" && $ticket02 != "����") ) {
            $ticket             = true;     // �������ͭ
        } else {
            $ticket             = false;    // �������̵
        }
        $approval_status        = 0;        // ��ǧ����
        $amano_input            = 0;        // ���ޥ����Ϥ�̵ͭ

        if( $content == "ͭ��ٲ�" || $content == "AMȾ��ͭ��ٲ�" || $content == "PMȾ��ͭ��ٲ�" ||
            $content == "����ñ��ͭ��ٲ�" || $content == "���"  || $content == "�ٹ�����" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu='%s', special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $yukyu, $remarks, $date, $uid);

        } else if( $content == "��ĥ���������" || $content == "��ĥ�ʽ����"
            || $content == "ľ��" || $content == "ľ��" || $content == "ľ��/ľ��" ) {
            if(!$ticket02) $ticket02 = "����";

            if( $content == "ľ��" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time=NULL, content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            } else if( $content == "ľ��" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time=NULL, end_date='%s', end_time='%s', content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $end_date, $end_time, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            } else {
                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, ticket01='%s', ticket02='%s', special=NULL, others='%s', place='%s', purpose='%s', doukousya='%s', remarks='%s', ticket='%b' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $ticket01, $ticket02, $others, $place, $purpose, $doukousya, $remarks, $ticket, $date, $uid);
            }
        } else if( $content == "���̵ٲ�" ) {
            if( $special != "����¾" ) {

                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special='%s', others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $special, $remarks, $date, $uid);

            } else {

                $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special='%s', others='%s', place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $special, $others, $remarks, $date, $uid);

            }
        } else if( $content == "���ص���" || $content == "����¾" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others='%s', place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $others, $remarks, $date, $uid);

        } else if( $content == "ID�������̤�˺��ʽжС�" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time=NULL, content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $content, $remarks, $date, $uid);

        } else if( $content == "ID�������̤�˺�����С�" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time=NULL, end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $end_date, $end_time, $content, $remarks, $date, $uid);

        } else if( $content == "���¾�ǧ˺��ʻĶȿ���ϳ���" || $content == "ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���" ) {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $remarks, $date, $uid);

        } else {

            $update_qry = sprintf("UPDATE sougou_deteils SET start_date='%s', start_time='%s', end_date='%s', end_time='%s', content='%s', yukyu=NULL, special=NULL, others=NULL, place=NULL, purpose=NULL, doukousya=NULL, remarks='%s' WHERE date='%s' AND uid='%s'", $start_date, $start_time, $end_date, $end_time, $content, $remarks, $date, $uid);

        }

        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] = "����Ϥι����˼��Ԥ��ޤ�����" . $update_qry;
            return false;
        }

        if( $ticket ) {
            if( $ticket01 != "����" && $ticket02 != "����") {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket01_set, $ticket02_set, $date, $uid);
            } else if( $ticket01 != "����" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set='%s', ticket02_set=NULL WHERE date='%s' AND uid='%s'", $ticket01_set, $date, $uid);
            } else if( $ticket02 != "����" ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01_set=NULL, ticket02_set='%s' WHERE date='%s' AND uid='%s'", $ticket02_set, $date, $uid);
            }
        } else {
            if( $place != '' ) {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01='����', ticket02='����', ticket01_set=NULL, ticket02_set=NULL, ticket='%d' WHERE date='%s' AND uid='%s'", $ticket, $date, $uid);
            } else {
                $update_qry = sprintf("UPDATE sougou_deteils SET ticket01=NULL, ticket02=NULL, ticket01_set=NULL, ticket02_set=NULL, ticket=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
            }
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "���������Ͽ�˼��Ԥ��ޤ�����";
        }

        if( $contact == "����¾" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other='%s', contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_other, $contact_tel, $date, $uid);
        } else if( $contact == "��ĥ��" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other=NULL, contact_tel='%s' WHERE date='%s' AND uid='%s'", $contact, $contact_tel, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET contact='%s', contact_other=NULL, contact_tel=NULL WHERE date='%s' AND uid='%s'", $contact, $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "Ϣ�������Ͽ�˼��Ԥ��ޤ�����";
        }

        if( $received_phone == "���ż�" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone='%s', received_phone_date='%s', received_phone_name='%s' WHERE date='%s' AND uid='%s'", $received_phone, $received_phone_date, $received_phone_name, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET received_phone=NULL, received_phone_date=NULL, received_phone_name=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "���żԤ���Ͽ�˼��Ԥ��ޤ�����";
        }

        if( $hurry == "���" ) {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry='%s' WHERE date='%s' AND uid='%s'", $hurry, $date, $uid);
        } else {
            $update_qry = sprintf("UPDATE sougou_deteils SET hurry=NULL WHERE date='%s' AND uid='%s'", $date, $uid);
        }
        if( query_affected($update_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "��ޤ���Ͽ�˼��Ԥ��ޤ�����";
        }
    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // Class Sougou_Admit_Model End

?>
