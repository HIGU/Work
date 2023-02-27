<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                                                              MVC Model ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_Model.php                                  //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class


/******************************************************************************
*          ����ϡʾȲ���� MVC��Model�� base class ���쥯�饹�����          *
******************************************************************************/
class Sougou_Query_Model extends ComTableMnt
{
    ///// Private properties
    private $indx;
    private $rows;
    private $res;
    private $uid;
    private $act_id;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $uid='')
    {
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        if ($uid == '') {
            return;    // �����ե�����ɤ����ꤵ��Ƥ��ʤ���в��⤷�ʤ�
        } else {
            $uid = sprintf('%06s', $uid);
            if( $uid == 0 ) return;
            $this->uid = $uid;    // Properties�ؤ���Ͽ
            $request->add('uid', $uid);
            $this->setActID();    // Properties�ؤ���Ͽ
        }

        switch ($request->get('showMenu')) {
        case 'List':
        case 'Results'  :

        default      :
            $sql_sum = "
                SELECT count(*) FROM sougou_deteils
            ";
            break;
        }

        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'sougou_query.log');
    }

    // �Ȳ���̤�ɽ�����������DB���
    public function getIndx()
    {
        return $this->indx;
    }

    // �Ȳ���̤�ɽ�����������DB�Կ�
    public function getRows()
    {
        return $this->rows;
    }

    // �Ȳ���̤�ɽ�����������DB
    public function getRes()
    {
        return $this->res;
    }

    // ������桼����ID
    public function getUid()
    {
        return $this->uid;
    }

    // ������桼����actID���å�
    public function setActID()
    {
        $query = "
            SELECT    act_id
            FROM      cd_table
            WHERE     uid = '$this->uid'
        ";
        $res = array();

        if ( $this->getResult2($query, $res) <= 0 ) {
            $this->act_id = "";
        } else {
            $this->act_id = $res[0][0];
        }
    }

    // ������桼����actID����
    public function getActID()
    {
        return $this->act_id;
    }

    // ɽ����ǽ���ܤǤ�����
    public function IsDisp($no)
    {
        if( $this->IsMaster() ) return true;

        $flag = false;
        switch ($no) {
            case  0:    // ����ʤ� �ʤ��٤ơ�
                break;
            case  1:    // �������칩��
                break;
            case  2:    // �ɣӣϻ�̳��
                if( $this->IsISO() ) $flag = true;
                break;
            case  3:    // ������
                break;
            case  4:    // ������ (������)
                break;
            case  5:    // ������ ��̳��
                break;
            case  6:    // ������ ���ʴ�����
                if( $this->IsKanriSyou() ) $flag = true;
                break;
            case  7:    // ������
                if( $this->IsGijyutsu() ) $flag = true;
                break;
            case  8:    // ������ (������)
                if( $this->IsGijyutsu() ) $flag = true;
                break;
            case  9:    // ������ �ʼ��ݾڲ�
                if( $this->IsGiHin() ) $flag = true;
                break;
            case 10:    // ������ ���Ѳ�
                if( $this->IsGiGi() ) $flag = true;
                break;
            case 11:    // ��¤��
                if( $this->IsSeizou() ) $flag = true;
                break;
            case 12:    // ��¤�� (��¤��)
                if( $this->IsSeizou() ) $flag = true;
                break;
            case 13:    // ��¤�� ��¤����
                if( $this->IsSeizouOne() ) $flag = true;
                break;
            case 14:    // ��¤�� ��¤����
                if( $this->IsSeizouTow() ) $flag = true;
                break;
            case 15:    // ������
                if( $this->IsSeisan() ) $flag = true;
                break;
            case 16:    // ������ (������)
                if( $this->IsSeisan() ) $flag = true;
                break;
            case 17:    // ������ ����������
                if( $this->IsSeiKanri() ) $flag = true;
                break;
            case 18:    // ������ ���ץ���Ω��
                if( $this->IsSeiCapura() ) $flag = true;
                break;
            case 19:    // ������ ��˥���Ω��
                if( $this->IsSeiLinia() ) $flag = true;
                break;
        }
        return $flag;
    }

    // �ޥ��������ʼҰ��ֹ����ϲ�ǽ�ԡʹ���Ĺ������������̳�ݡˡ�
    public function IsMaster()
    {
        $flag = false;
        if( getCheckAuthority(63) ) $flag = true;
/**
        switch ($this->act_id) {
            case 600:   // ����Ĺ
            case 610:   // ������
            case 650:   // ������ ��̳��
            case 651:   // ������ ��̳�� ��̳ô��
            case 660:   // ������ ��̳�� ��̳ô��
                $flag = true;
        }
/**/
        return $flag;
    }

    // �ɣӣϻ�̳�ɡ�
    public function IsISO()
    {
        $flag = false;
        switch ($this->act_id) {
            case 605:   // �ɣӣϻ�̳��
                $flag = true;
        }
        return $flag;
    }

    // ������ ���ʴ����ݡ�
    public function IsKanriSyou()
    {
        $flag = false;
        switch ($this->act_id) {
            case 670:   // ���ʴ�����
                $flag = true;
        }
        return $flag;
    }

    // ��������
    public function IsGijyutsu()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // ������
                $flag = true;
        }
        return $flag;
    }

    // ������ �ʼ������ݡ�
    public function IsGiHin()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // ������
            case 174:   // ������ �ʼ�������
            case 517:   // ������ �ʼ������� ���ץ鸡��ô��
                $flag = true;
        }
        return $flag;
    }

    // ������ ���Ѳݡ�
    public function IsGiGi()
    {
        $flag = false;
        switch ($this->act_id) {
            case 501:   // ������
            case 173:   // ������ ���Ѳ�
                $flag = true;
        }
        return $flag;
    }

    // ��¤����
    public function IsSeizou()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // ��¤��
                $flag = true;
        }
        return $flag;
    }

    // ��¤�� ��¤���ݡ�
    public function IsSeizouOne()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // ��¤��
            case 518:   // ��¤�� ��¤����
                $flag = true;
        }
        return $flag;
    }

    // ��¤�� ��¤���ݡ�
    public function IsSeizouTow()
    {
        $flag = false;
        switch ($this->act_id) {
            case 582:   // ��¤��
            case 547:   // ��¤�� ��¤����
                $flag = true;
        }
        return $flag;
    }

    // ��������
    public function IsSeisan()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // ������
                $flag = true;
        }
        return $flag;
    }

    // ������ ���������ݡ�
    public function IsSeiKanri()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // ������
            case 545:   // ������ ����������
                $flag = true;
        }
        return $flag;
    }

    // ������ ���ץ���Ω�ݡ�
    public function IsSeiCapura()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // ������
            case 176:   // ������ ���ץ���Ω��
                $flag = true;
        }
        return $flag;
    }

    // ������ ��˥���Ω�ݡ�
    public function IsSeiLinia()
    {
        $flag = false;
        switch ($this->act_id) {
            case 500:   // ������
            case 551:   // ������ ��˥���Ω��
                $flag = true;
        }
        return $flag;
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

    // ���ޥ����� �� �˹����������
    public function AmanoRun(&$result, $request)
    {
        if( $request->get('amano_run') != 'true') return true;

        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // ���ϲ��⤷�ʤ���

        // ��ä�������Ϥ�����������ǧ�����˼�ä򥻥å�
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get('amano' . $r) != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }
                // ���ޥ����Ϥ� �� ���ѹ�
                $update_qry = sprintf("UPDATE sougou_deteils SET amano_input = 't' WHERE date='%s' AND uid='%s'", $last_res[$r][0], $last_res[$r][1]);
//                $_SESSION['s_sysmsg'] .= $update_qry;
                if( query_affected($update_qry) <= 0 ) {
                    return false;
                }
            }
        }

        return true;
    }

    // ��ý����μ¹�
    public function CancelRun(&$result, $request)
    {
        if( $request->get('cancel_run') != 'true') return true;

        $last_indx = $request->get('indx');
        $last_rows = $request->get('rows');

        if( $last_rows == '' ) return; // ���ϲ��⤷�ʤ���

        // ��ä�������Ϥ�����������ǧ�����˼�ä򥻥å�
        $last_res = array();
        for( $r=0; $r<$last_rows; $r++ ) {
            if( $request->get($r) != '' ) {
                $res = $request->get(sprintf("res-%s", $r));
                for( $i=0; $i<$last_indx; $i++ ) {
                    $last_res[$r][$i] = $res[$i];
                }
                // ��ǧ������CANCEL���ѹ�
                $update_qry = sprintf("UPDATE sougou_deteils SET admit_status = '%s' WHERE date='%s' AND uid='%s'", 'CANCEL', $last_res[$r][0], $last_res[$r][1]);
//                $_SESSION['s_sysmsg'] .= $update_qry;
                if( query_affected($update_qry) <= 0 ) {
                    return false;
                }

                $query = "SELECT date FROM admit_stop_reason WHERE date='{$last_res[$r][0]}' AND uid='{$last_res[$r][1]}'";
                if( getResult2($query, $res_chk) <= 0 ) { // �ޤ�����Ͽ����Ƥ��ʤ�
                    $insert_qry = "
                        INSERT INTO admit_stop_reason (date, uid, state, reason) VALUES
                            ('{$last_res[$r][0]}', '{$last_res[$r][1]}', 'CANCEL', '�Ȳ�����');
                    ";
                    if( query_affected($insert_qry) <= 0 ) {
                        $_SESSION['s_sysmsg'] .= "�����ͳ����Ͽ�˼��Ԥ��ޤ�����";
                    }
                } else {
                    // ��ǧ��ͳ������ʤ�񤭴����ʤ���
                }
            }
        }

        return true;
    }

    // �����̤�actID����
    public function getBumonActID($name)
    {
        $where = "";

        if( $name == '�������칩��' ) {
            $where = "(at.act_id=600) ";
        } else if( $name == '�ɣӣϻ�̳��' ) {
            $where = "(at.act_id=605) ";
        } else if( $name == '������' ) {
            $where = "(at.act_id=610 OR at.act_id=650 OR at.act_id=651 OR at.act_id=660 OR at.act_id=670) ";
        } else if( $name == '������ (������)' ) {
            $where = "(at.act_id=610) ";
        } else if( $name == '������ ��̳��' ) {
            $where = "(at.act_id=650 OR at.act_id=651 OR at.act_id=660) ";
        } else if( $name == '������ ���ʴ�����' ) {
            $where = "(at.act_id=670) ";
        } else if( $name == '������' ) {
            $where = "(at.act_id=501 OR at.act_id=174 OR at.act_id=517 OR at.act_id=537 OR at.act_id=173 OR at.act_id=515 OR at.act_id=535 OR at.act_id=581) ";
        } else if( $name == '������ (������)' ) {
            $where = "(at.act_id=501) ";
        } else if( $name == '������ �ʼ��ݾڲ�' ) {
            $where = "(at.act_id=174 OR at.act_id=517 OR at.act_id=537 OR at.act_id=581) ";
        } else if( $name == '������ ���Ѳ�' ) {
            $where = "(at.act_id=173 OR at.act_id=515 OR at.act_id=535) ";
        } else if( $name == '��¤��' ) {
            $where = "(at.act_id=582 OR at.act_id=518 OR at.act_id=519 OR at.act_id=556 OR at.act_id=520 OR at.act_id=547 OR at.act_id=527 OR at.act_id=528) ";
        } else if( $name == '��¤�� (��¤��)' ) {
            $where = "(at.act_id=582) ";
        } else if( $name == '��¤�� ��¤����' ) {
            $where = "(at.act_id=518 OR at.act_id=519 OR at.act_id=556 OR at.act_id=520) ";
        } else if( $name == '��¤�� ��¤����' ) {
            $where = "(at.act_id=547 OR at.act_id=527 OR at.act_id=528) ";
        } else if( $name == '������' ) {
            $where = "(at.act_id=500 OR at.act_id=545 OR at.act_id=512 OR at.act_id=532 OR at.act_id=513 OR at.act_id=533 OR at.act_id=514 OR at.act_id=534 OR at.act_id=176 OR at.act_id=522 OR at.act_id=523 OR at.act_id=525 OR at.act_id=551 OR at.act_id=175 OR at.act_id=572) ";
        } else if( $name == '������ (������)' ) {
            $where = "(at.act_id=500) ";
        } else if( $name == '������ ����������' ) {
            $where = "(at.act_id=545 OR at.act_id=512 OR at.act_id=532 OR at.act_id=513 OR at.act_id=533 OR at.act_id=514 OR at.act_id=534) ";
        } else if( $name == '������ ���ץ���Ω��' ) {
            $where = "(at.act_id=176 OR at.act_id=522 OR at.act_id=523 OR at.act_id=525) ";
        } else if( $name == '������ ��˥���Ω��' ) {
            $where = "(at.act_id=551 OR at.act_id=175 OR at.act_id=572) ";
        }

        return $where;
    }

    // ��Ĺ�����ʾ塩
    public function IsBukatyou()
    {
        $query = "
            SELECT          ct.act_id
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           ud.uid = '$this->uid' AND (ud.pid=46 OR ud.pid=47 OR ud.pid=50 OR ud.pid=70 )
        ";
        $res = array();

        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }

        return true;
    }

    // �Ȳ���̤�ɽ�����������DB����
    public function getViewDataList(&$result, $request)
    {
        $si_s_date = $request->get('si_s_date');
        $si_e_date = $request->get('si_e_date');
        if( strlen($request->get('syainbangou')) == 0 ) {
            $syainbangou = '';
        } else {
            $syainbangou = sprintf('%06s', $request->get('syainbangou'));
        }
        $simei = $request->get('simei');
        $str_date = $request->get('str_date');
        $end_date = $request->get('end_date');
        $ddlist = $request->get('ddlist');
        if( $ddlist == "����ʤ�" ) $ddlist = '';
        $ddlist_bumon = $request->get('ddlist_bumon');
        if( $ddlist_bumon == "����ʤ� �ʤ��٤ơ�" ) $ddlist_bumon = '';
        $r5 = $request->get('r5');
        if( $r5 == "����ʤ�" ) $r5 = '';
        $r6 = $request->get('r6');
        if( $r6 == "����ʤ�" ) $r6 = '';
        $r7 = $request->get('r7');
        if( $r7 == "����ʤ�" ) $r7 = '';
        $r8 = $request->get('r8');
        if( $r8 == "����ʤ�" ) $r8 = '';
        $r9 = $request->get('r9');
        if( $r9 == "����ʤ�" ) $r9 = '';

        $where = '';
        if( $si_s_date != '' ) {
            if( $si_e_date != '' ) {
                $where .= sprintf( "date >= '%s 00:00:00' AND date <= '%s 23:59:59' ", $si_s_date, $si_e_date );
            } else {
                $where .= sprintf( "date >= '%s 00:00:00' AND date <= '%s 23:59:59' ", $si_s_date, $si_s_date );
            }
        }
        if( $syainbangou != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "uid = '%s' ", $syainbangou );
        }

        if( $simei != '' ) {
            $query = sprintf( "SELECT uid FROM user_detailes WHERE retire_date IS NULL AND( name like '%%%s%%' OR kana like '%%%s%%' OR spell like '%%%s%%')", $simei, $simei, $simei );
            $res = array();
            if ( ($rows=getResultWithField2($query, $field, $res)) > 0 ) {
                if( $where != '' ) $where .= 'AND ';
                $where .= sprintf( "(uid = '%s' ", $res[0][0] );
                for( $i=1; $i < $rows; $i++ ) {
                    $where .= sprintf( "OR uid = '%s' ", $res[$i][0] );
                }
                $where .= ') ';
            } else {
                $_SESSION['s_sysmsg'] .= '�ե�͡���ξ��ϡ���̾�δ֤˥��ڡ���������Ƥ���������';
                return false;
            }
        }

        if( $str_date != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $end_date != '' ) {
//                $where .= sprintf( "((start_date >= '%s' AND end_date <= '%s') OR (start_date >= '%s' AND start_date <= '%s') OR (end_date >= '%s' AND end_date <= '%s'))", $str_date, $end_date, $str_date, $end_date, $str_date, $end_date );
                $where .= sprintf( "((start_date >= '%s' AND end_date <= '%s') OR (start_date <= '%s' AND end_date >= '%s') OR (start_date <= '%s' AND end_date >= '%s') OR (start_date >= '%s' AND start_date <= '%s') OR (end_date >= '%s' AND end_date <= '%s'))", $str_date, $end_date, $str_date, $str_date, $end_date, $end_date, $str_date, $end_date, $str_date, $end_date );
            } else {
//                $where .= sprintf( "start_date = '%s' ", $str_date );
                $where .= sprintf( "(start_date = '%s' OR (start_date <= '%s' AND end_date >= '%s')) ", $str_date, $str_date, $str_date );
            }
        }

        if( $ddlist != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "content = '%s' ", $ddlist );
        }

        if( $r6 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r6 == 't' ) {
//            if( $r6 ) {
                $where .= sprintf( "ticket = '%s' ", $r6 );
            } else {
//                $where .= "(ticket != 't' OR ticket IS NULL) ";
                $where .= "ticket = 'f' ";
//                $where .= "ticket IS NULL ";
            }
        }
        if( $r7 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r7 == '���ż�' ) {
                $where .= sprintf( "received_phone = '%s' ", $r7 );
            } else {
                $where .= "received_phone IS NULL ";
            }
        }
        if( $r8 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r8 == '���' ){
                $where .= sprintf( "hurry = '%s' ", $r8 );
            } else {
                $where .= "hurry IS NULL ";
            }
        }
        if( $r9 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r9 == 'END' || $r9 == 'DENY' || $r9 == 'CANCEL' ) {
                $where .= sprintf( "admit_status = '%s' ", $r9 );
            } else {
                $where .= "(admit_status != 'END' AND admit_status != 'DENY' AND admit_status != 'CANCEL') ";
            }
        }

        if( $r5 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r5 == '�ѡ��Ȱʳ�' ) {
                $where .= "ud.pid!=5 AND ud.pid!=6 ";
            } else {
                $where .= "(ud.pid=5 OR ud.pid=6) ";
            }
        }

        if( $ddlist_bumon != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= $this->getBumonActID($ddlist_bumon);
        }

        if( $where != '' ) {
            $where = "WHERE " . $where;
        }

        $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY ct.orga_id ASC, ud.uid ASC, sd.start_date ASC", $where );
/*
        if( $ddlist_bumon != '' && $r5 != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else if( $ddlist_bumon != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else if( $r5 != '' ) {
            $query = sprintf( "SELECT sd.* FROM sougou_deteils AS sd LEFT OUTER JOIN user_detailes AS ud USING(uid) %s ORDER BY sd.uid ASC, sd.start_date ASC", $where );
        } else {
            $query = sprintf( "SELECT * FROM sougou_deteils %s ORDER BY uid ASC, start_date ASC", $where );
        }
*/
//        $_SESSION['s_sysmsg'] .= '���� : ' . $query;

        $res = array();
        if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
            ; // $_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
        } else {
            ; // $_SESSION['s_sysmsg'] .= $rows . '�濫��ޤ���';
        }
        $result->add_array($res);

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
    }

/**/
    // �Ȳ���̤�ɽ�����������DB����
    public function getHuzaisyaDataList(&$result, $request)
    {
        $si_s_date = $request->get('si_s_date');
        $si_e_date = $request->get('si_e_date');
        if( strlen($request->get('syainbangou')) == 0 ) {
            $syainbangou = '';
        } else {
            $syainbangou = sprintf('%06s', $request->get('syainbangou'));
        }
        $str_date = $request->get('str_date');
        $end_date = $request->get('end_date');
        $r5 = $request->get('r5');
        if( $r5 == "����ʤ�" ) $r5 = '';

        $where = '';
        if( $si_s_date != '' ) {
            if( $si_e_date != '' ) {
                $where .= sprintf( "sd.date >= '%s 00:00:00' AND sd.date <= '%s 23:59:59' ", $si_s_date, $si_e_date );
            } else {
                $where .= sprintf( "sd.date >= '%s 00:00:00' AND sd.date <= '%s 23:59:59' ", $si_s_date, $si_s_date );
            }
        }
        if( $syainbangou != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= sprintf( "sd.uid = '%s' ", $syainbangou );
        }

        if( $str_date != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $end_date != '' ) {
                $where .= sprintf( "((sd.start_date >= '%s' AND sd.end_date <= '%s') OR (sd.start_date <= '%s' AND sd.end_date >= '%s') OR (sd.start_date <= '%s' AND sd.end_date >= '%s') OR (sd.start_date >= '%s' AND sd.start_date <= '%s') OR (sd.end_date >= '%s' AND sd.end_date <= '%s'))", $str_date, $end_date, $str_date, $str_date, $end_date, $end_date, $str_date, $end_date, $str_date, $end_date );
            } else {
                $where .= sprintf( "(sd.start_date = '%s' OR (sd.start_date <= '%s' AND sd.end_date >= '%s')) ", $str_date, $str_date, $str_date );
            }
        }

        if( $where != '' ) $where .= 'AND ';
        $where .= "sd.admit_status != 'CANCEL' AND sd.admit_status != 'DENY' ";

        if( $r5 != '' ) {
            if( $where != '' ) $where .= 'AND ';
            if( $r5 == '�ѡ��Ȱʳ�' ) {
                $where .= "ud.pid!=5 AND ud.pid!=6 ";
            } else {
                $where .= "(ud.pid=5 OR ud.pid=6) ";
            }
        }

        $ddlist_bumon = $request->get('ddlist_bumon');
        if( $ddlist_bumon == "����ʤ� �ʤ��٤ơ�" ) $ddlist_bumon = '';
        if( $ddlist_bumon != '' ) {
            if( $where != '' ) $where .= 'AND ';
            $where .= $this->getBumonActID($ddlist_bumon);
        }

        if( $where != '' ) {
            $where = "WHERE " . $where . "AND ";
        }

        $query = sprintf( "
                SELECT ct.orga_id, sm.section_name, ud.uid, ud.name, sd.start_date, sd.end_date, sd.content, sd.start_time, sd.end_time
                FROM                sougou_deteils  AS sd
                LEFT OUTER JOIN     user_detailes   AS ud    USING(uid)
                LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
                LEFT OUTER JOIN     section_master  AS sm    USING(sid)
                LEFT OUTER JOIN     act_table       AS at    USING(act_id)
                %s    ct.orga_id IS NOT NULL
                  AND sd.content!='ID�������̤�˺��ʽжС� '
                  AND sd.content!='ID�������̤�˺�����С� '
                  AND sd.content!='���¾�ǧ˺��ʻĶȿ���ϳ���'
                  AND sd.content!='ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���'
                ORDER BY ct.orga_id ASC, ud.uid ASC, sd.start_date ASC
        ", $where );
//$_SESSION['s_sysmsg'] .= '���� : ' . $query;

        $res = array();
        if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
            ; //$_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
        } else {
            ; //$_SESSION['s_sysmsg'] .= $rows . '�濫��ޤ���';
        }
        $result->add_array($res);

        $this->indx = count($field);
        $this->rows = $rows;
        $this->res = $res;

        return $rows;
    }
/**/

    // ��°̾����
    public function getSyozoku($uid)
    {
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
//            $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
            return "------";
        }
        return $res[0][0];
    }

    // �Ұ���̾�������
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

    // ��������Ϥ��Ф�����ǧ��ͳ�μ���
    public function getAdmitStopReason($date, $uid, $status)
    {
        $query = "
            SELECT      reason
            FROM        admit_stop_reason
            WHERE       date = '$date' AND uid = '$uid'
        ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '---';
        }
        if($status == '���') return $res[0][0];

        $hito = $this->getAdmitStop($date, $uid);

        return '***** ' . $hito . ' �� ������ *****<br>' . $res[0][0];
    }

    // ��������Ϥ��Ф�����ǧ�Ԥμ���
    public function getAdmitStop($date, $uid)
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
/**
        if( $res[0][13] != "------" ) if( $res[0][14] != '' ) return $res[0][13];   // ����Ĺ
        if( $res[0][11] != "------" ) if( $res[0][12] != '' ) return $res[0][11];   // ������Ĺ
        if( $res[0][ 9] != "------" ) if( $res[0][10] != '' ) return $res[0][ 9];    // ��̳��Ĺ
        if( $res[0][ 7] != "------" ) if( $res[0][ 8] != '' ) return $res[0][ 7];    // ��°��Ĺ
        if( $res[0][ 5] != "------" ) if( $res[0][ 6] != '' ) return $res[0][ 5];    // ��°��Ĺ
        if( $res[0][ 3] != "------" ) if( $res[0][ 4] != '' ) return $res[0][ 3];    // ��°��Ĺ
/**/
        if( $res[0][13] != "------" ) if( $res[0][14] != '' ) return $this->getSyainName($res[0][13]);   // ����Ĺ
        if( $res[0][11] != "------" ) if( $res[0][12] != '' ) return $this->getSyainName($res[0][11]);   // ������Ĺ
        if( $res[0][ 9] != "------" ) if( $res[0][10] != '' ) return $this->getSyainName($res[0][ 9]);    // ��̳��Ĺ
        if( $res[0][ 7] != "------" ) if( $res[0][ 8] != '' ) return $this->getSyainName($res[0][ 7]);    // ��°��Ĺ
        if( $res[0][ 5] != "------" ) if( $res[0][ 6] != '' ) return $this->getSyainName($res[0][ 5]);    // ��°��Ĺ
        if( $res[0][ 3] != "------" ) if( $res[0][ 4] != '' ) return $this->getSyainName($res[0][ 3]);    // ��°��Ĺ
/**/
        return '';

    }

    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // Class Sougou_Query_Model End

?>
