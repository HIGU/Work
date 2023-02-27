<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���                                                           //
//                                                              MVC Model ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Model.php                         //
// 2021/11/01 Release.                                                        //
// 2021/11/25 ɽ���������Ū���ѹ� 970328                                   //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class

/******************************************************************************
*          ����ϡʿ������� MVC��Model�� base class ���쥯�饹�����          *
******************************************************************************/
class over_time_work_report_Model extends ComTableMnt
{
    ///// Private properties
    private $uid;       // ������桼����ID
    private $act_id;    // ����ID
    private $deploy;    // ����̾
    private $posts_na;  // ����̾
    private $posts_no;  // ����No
    private $str_hour;  // ���� ��
    private $str_min;   // ���� ʬ
    private $end_hour;  // ��λ ��
    private $end_min;   // ��λ ʬ
    private $show_menu; // ɽ���⡼��
    private $hurry;     // ��ޥե饰
    
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
            $this->setActID();
            $this->setPosts();
            $request->add('uid', $uid);
        }

        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $this->show_menu = $request->get('showMenu');   // �������åȥ�˥塼�����

        $sql_sum = "
            SELECT count(*) FROM user_detailes where uid like '%{$uid}'
        ";
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'over_time_work_report.log');
    }
    
// ============================================================================
// �����ǻ��Ѥ���ؿ� =========================================================
// ============================================================================
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
    
    // ������桼�������̤򥻥å�
    public function setPosts()
    {
        $this->posts_na = '';
        $this->posts_no = 0;
        if( $this->IsKatyou() ) {
            $this->posts_na = 'ka';
            $this->posts_no = 1;
        } else if( $this->IsButyou() ) {
            $this->posts_na = 'bu';
            $this->posts_no = 2;
        } else if( $this->IsKoujyoutyou() ) {
            $this->posts_na = 'ko';
            $this->posts_no = 3;
        }
    }
    
    // ������桼����actID�����
    public function getActID()
    {
        return $this->act_id;
    }
    
    // ɽ���⡼�ɤ����
    public function getShowMenu()
    {
        return $this->show_menu;
    }
    
    // ɽ����������̾�ꥹ�ȼ���
    public function getBumonName()
    {
        return array("��̳��", "���ʴ�����", "�ʼ��ݾڲ�", "���Ѳ�", "��¤�� ��¤����", "��¤�� ��¤����", "���������� �ײ衦���㷸", "���������� ��෸", "���ץ���Ω�� ɸ�෸�ͣ�", "���ץ���Ω�� ɸ�෸�ȣ�", "���ץ���Ω�� ����", "��˥���Ω��");
    }
    
    // �Ұ��Ǥ�����
    public function IsSyain($uid)
    {
        $query = "SELECT uid FROM user_detailes WHERE uid = '$uid' AND retire_date IS NULL";
        if( getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }
    
    // �����̤�actID����
    public function getBumonActID($name)
    {
        $where = "";
        
        if( $name == "��̳��" ) {
            $where = "(ct.act_id=605 OR ct.act_id=610 OR ct.act_id=650 OR ct.act_id=651 OR ct.act_id=660) ";
        } else if( $name == "���ʴ�����" ) {
            $where = "(ct.act_id=670) ";
        } else if( $name == "�ʼ��ݾڲ�" ) {
            $where = "(ct.act_id=501 OR ct.act_id=174 OR ct.act_id=517 OR ct.act_id=537 OR ct.act_id=581) ";
        } else if( $name == "���Ѳ�" ) {
            $where = "(ct.act_id=173 OR ct.act_id=515 OR ct.act_id=535) ";
        } else if( $name == "��¤�� ��¤����" ) {
            $where = "(ct.act_id=518 OR ct.act_id=519 OR ct.act_id=556 OR ct.act_id=520) ";
        } else if( $name == "��¤�� ��¤����" ) { // 600 ��ޤ�Ǥ���١����ˡ�"AND ud.uid!=999999 AND ud.pid!=110" �ɲ�
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=547 OR ct.act_id=527 OR ct.act_id=528) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $name == "���������� �ײ衦���㷸" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=512 OR ct.act_id=532 OR ct.act_id=513 OR ct.act_id=533) ";
        } else if( $name == "���������� ��෸" ) {
            $where = "(ct.act_id=514 OR ct.act_id=534) ";
        } else if( $name == "���ץ���Ω�� ɸ�෸�ͣ�" ) {
//            $where = "(ct.act_id=522) ";
            $where = "((ct.act_id=522) OR (ct.act_id=523 AND uid='970328')) ";  // �ֿ� ��Ҥ���׶���Ū�ˡ��ͣ���ɽ��
        } else if( $name == "���ץ���Ω�� ɸ�෸�ȣ�" ) {
//            $where = "(ct.act_id=176 OR ct.act_id=523) ";
            $where = "(ct.act_id=176 OR (ct.act_id=523 AND uid!='970328')) ";   // �ֿ� ��Ҥ���׶���Ū�ˡ��ȣ��������
        } else if( $name == "���ץ���Ω�� ����" ) {
            $where = "(ct.act_id=525) ";
        } else if( $name == "��˥���Ω��" ) {
            $where = "(ct.act_id=551 OR ct.act_id=175 OR ct.act_id=572) ";
        }
        
        return $where;
    }
    
    // �ޥ��������ʹ���Ĺ������������̳�ݡ�
    public function IsMaster()
    {
        $flag = false;
        $show_menu = $this->getShowMenu();
        switch ($this->act_id) {
            case 610:   // ������
            case 650:   // ������ ��̳��
            case 651:   // ������ ��̳�� ��̳ô��
            case 660:   // ������ ��̳�� ��̳ô��
                if( $show_menu == 'Quiry' || $show_menu == 'Results' ) $flag = true;
                break;
            case 600:   // ����Ĺ
                if( $this->uid == '012394') {
                    $this->act_id = 582;
                    break;
                }
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // �ɣӣϻ�̳�ɡ�
    public function IsISO()
    {
        switch ($this->act_id) {
            case 610:   // ������
            case 605:   // �ɣӣϻ�̳��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ��̳��
    public function IsSoumu()
    {
        switch ($this->act_id) {
            case 605:   // �ɣӣϻ�̳��
            case 610:   // ������
            case 650:   // ������ ��̳��
            case 651:   // ������ ��̳�� ��̳
            case 660:   // ������ ��̳�� ��̳
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���ʴ����ݡ�
    public function IsKanriSyou()
    {
        switch ($this->act_id) {
            case 610:   // ������
            case 670:   // ������ ���ʴ�����
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ �ʼ��ݾڲݡ�
    public function IsGiHin()
    {
        switch ($this->act_id) {
            case 501:   // ������
            case 174:   // ������ �ʼ��ݾڲ�
            case 517:   // ������ �ʼ��ݾڲ� ���ץ鸡��ô��
            case 537:   // ������ �ʼ��ݾڲ� ���ץ鸡��ô��
            case 581:   // ������ �ʼ��ݾڲ� ���ץ鸡��ô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���Ѳݡ�
    public function IsGiGi()
    {
        switch ($this->act_id) {
            case 501:   // ������
            case 173:   // ������ ���Ѳ�
            case 515:   // ������ ���Ѳ�
            case 535:   // ������ ���Ѳ�
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ��¤�� ��¤���ݡ�
    public function IsSeizouOne()
    {
        switch ($this->act_id) {
            case 582:   // ��¤��
            case 518:   // ��¤�� ��¤����
            case 519:   // ��¤�� ��¤����
            case 556:   // ��¤�� ��¤����
            case 520:   // ��¤�� ��¤����
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ��¤�� ��¤���ݡ�
    public function IsSeizouTow()
    {
        switch ($this->act_id) {
            case 582:   // ��¤��
            case 547:   // ��¤�� ��¤����
            case 528:   // ��¤�� ��¤����
            case 527:   // ��¤�� ��¤����
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���������� �ײ衦���㷸��
    public function IsSeiKanKeiKou()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 545:   // ������ ����������
            case 512:   // ������ ���������� �ײ跸 ��ô��
            case 532:   // ������ ���������� �ײ跸 ��ô��
            case 513:   // ������ ���������� ���㷸 ��ô��
            case 533:   // ������ ���������� ���㷸 ��ô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���������� ��෸��
    public function IsSeiKanSizai()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 545:   // ������ ����������
            case 514:   // ������ ���������� ��෸ ���ץ���
            case 534:   // ������ ���������� ��෸ ��˥����
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���ץ���Ω�� ɸ�෸MA��
    public function IsSeiCapuraMA()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 176:   // ������ ���ץ���Ω��
            case 522:   // ������ ���ץ���ΩMAô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���ץ���Ω�� ɸ�෸HA��
    public function IsSeiCapuraHA()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 176:   // ������ ���ץ���Ω��
            case 523:   // ������ ���ץ���ΩHAô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ���ץ���Ω�� ������
    public function IsSeiCapuraSC()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 176:   // ������ ���ץ���Ω��
            case 525:   // ������ ���ץ�����ô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ������ ��˥���Ω�ݡ�
    public function IsSeiLinia()
    {
        switch ($this->act_id) {
            case 500:   // ������
            case 551:   // ������ ��˥���Ω��
            case 175:   // ������ ��˥���Ωô��
            case 572:   // ������ �ԥ��ȥ���ô��
                $flag = true;
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ɽ����ǽ���ܤǤ�����
    public function IsDisp($no)
    {
        if( $this->IsMaster() ) return true;

        switch ($no) {
            case  0:    // ---- ���򤷤Ʋ����� ----
                $flag = true;
                break;
            case  1:    // ��̳��
                $flag = $this->IsSoumu();
                break;
            case  2:    // ���ʴ�����
                $flag = $this->IsKanriSyou();
                break;
            case  3:    // �ʼ��ݾڲ�
                $flag = $this->IsGiHin();
                break;
            case  4:    // ���Ѳ�
                $flag = $this->IsGiGi();
                break;
            case  5:    // ��¤�� ��¤����
                $flag = $this->IsSeizouOne();
                break;
            case  6:    // ��¤�� ��¤����
                $flag = $this->IsSeizouTow();
                break;
            case  7:    // ���������� �ײ衦���㷸
                $flag = $this->IsSeiKanKeiKou();
                break;
            case  8:    // ���������� ��෸
                $flag = $this->IsSeiKanSizai();
                break;
            case  9:    // ���ץ���Ω�� ɸ�෸�ͣ�
                $flag = $this->IsSeiCapuraMA();
                break;
            case 10:    // ���ץ���Ω�� ɸ�෸�ȣ�
                $flag = $this->IsSeiCapuraHA();
                break;
            case 11:    // ���ץ���Ω�� ����
                $flag = $this->IsSeiCapuraSC();
                break;
            case 12:    // ��˥���Ω��
                $flag = $this->IsSeiLinia();
                break;
            default:
                $flag = false;
                break;
        }
        return $flag;
    }

    // ����UID�Υ᡼�륢�ɥ쥹����
    public function getMailAddres($send_uid)
    {
        $query = "
                    SELECT          trim(name), trim(mailaddr)
                    FROM            user_detailes
                    LEFT OUTER JOIN user_master USING(uid)
                 ";
//        $search = "WHERE uid='300667'"; // ������ �����ѹ� ����꡼�����ϡ������Ȳ�
        $search = "WHERE uid='$send_uid'";    // ������
        $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            return "";
        }
        return trim($res[0][1]); // �᡼�륢�ɥ쥹
    }
    
    // �᡼������
    public function SendMail($mode, $send_uid, $date, $deploy, $uid, $type, $memo)
    {
        $send_uid = sprintf('%06s', $send_uid);
        
        $to_addres = $this->getMailAddres($send_uid);   // �᡼�륢�ɥ쥹
        $to_name   = $this->getName($send_uid);         // ����Ի�̾
        $add_head  = "";
        $attenSubject = "���衧 {$to_name} �� ����ֳ���ȿ����ꤪ�Τ餻";  // �����ȥ�
        // ����
        if( strlen($date) == 8 ) {
            $date = substr($date,0,4) . '-' . substr($date,4,2) . '-' . substr($date,6,2);
        }
        $date = $this->getTargetDateDay($date, 'on');   // YYYY-MM-DD (week)
        $name = $this->getName($uid);   // �оݼԻ�̾
        if( $name == "" ) $name = $uid;
        // $mode ������Ƥ��ѹ����롣
        switch ($mode) {
            case "Result":      // �Ķȷ�����ϴ�λ
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �����ϴ�λ�� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                $message .= "�ʲ�������ֳ���ȿ���ʻĶȷ�������������Ϥ���ޤ�����\n\n";
                $message .= "�������{$date}������$deploy\n\n";
                $message .= "��ǧ�����򤪴ꤤ�ޤ���\n\n";
                $message .= "������ URL�Ϥ����� ������\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=3\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}��������ϴ�λ����$deploy\t";
                break;
            case "AfterReport": // �������
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �ڻ������� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                $message .= "�ʲ�������ֳ���ȿ���ʻĶȷ�����˻��������ޤ�����\n\n";
                $message .= "�������{$date}������$deploy\t��ȼԡ�$name\n\n";
                $message .= "��ǧ�ξ塢��ǧ�����򤪴ꤤ�ޤ���\n\n";
                $message .= "������ URL�Ϥ����� ������\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}���������ο͡�$name\t";
                break;
            case "Cancel":// ���ä�
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �ڼ��ä��� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                $message .= "�ʲ�������ֳ���ȿ����";
                if( $type == "yo" ) $message .= "��������";
                if( $type == "ji" ) $message .= "�Ķȷ�����";
                $message .= "�ˤ����ä���ޤ�����\n\n";
                $message .= "�������{$date}������$deploy\t��ȼԡ�$name\n\n";
                $message .= "�����ͳ�ϡ��ʲ����̤�Ǥ���\n\n";
                $message .= "��$memo\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}�����ä��ԡ�$name\t";
                break;
            case "Hurry":// ���
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �ڻ�ޡ� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                if( $type == "" ) {
                    $message .= "�ʲ�������ֳ���ȿ���ʻ��������ˤ���ޤ�����\n\n";
                } else {
                    $message .= "{$memo} �� �Ժߤΰ١��ʲ�������ֳ���ȿ���ʻ���������\n\n";
                }
                if( $name ) {
                    $message .= "�������{$date}������$deploy\t��ȼԡ�$name\n\n";
                } else {
                    $message .= "�������{$date}������$deploy\n\n";
                }
                if( $type == "" ) {
                    $message .= "��ޡ���ǧ�����򤪴ꤤ�ޤ���\n\n";
                    $message .= "������ URL�Ϥ����� ������\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
                } else {
                    $message .= "��ޡ���ǧ�����򤪴ꤤ�ޤ����ʢ��Ժ�̤��ǧ����\n\n";
                    $message .= "������ URL�Ϥ����� ������\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=2\n\n";
                }
/**
if( $name ) {
$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}����ޤʿ͡�$name\t";
}else {
$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}����ޤ�����$deploy\t";
}
/**/
                break;
            case "Next":    // �������� ���ξ�ǧ�Ԥ��Τ餻��
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �ڻ��������� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                if( $type == "" ) {
                    $message .= "�ʲ�������ֳ���ȿ���ʻ��������ˤ���ޤ�����\n\n";
                } else {
                    $message .= "{$memo} �� �Ժߤΰ١��ʲ�������ֳ���ȿ���ʻ���������\n\n";
                }
                
                $message .= "�������{$date}������$deploy\n\n";
                
                if( $type == "" ) {
                    $message .= "��ǧ�����򤪴ꤤ�ޤ���\n\n";
                    $message .= "������ URL�Ϥ����� ������\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge\n\n";
                } else {
                    $message .= "��ǧ�����򤪴ꤤ�ޤ����ʢ��Ժ�̤��ǧ����\n\n";
                    $message .= "������ URL�Ϥ����� ������\n\n";
                    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$send_uid}&showMenu=Judge&select_radio=2\n\n";
                }
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}����������$deploy\t";
                break;
            case "Notice":  // �ǽ���ǧ
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� �ھ�ǧ�� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                $message .= "�ʲ�������ֳ���ȿ���ʻ��������ˤ���ǧ����ޤ�����\n\n";
                $message .= "�������{$date}������$deploy\n\n��ȼԡ�$name\n\n";
                $message .= "��ȼԤؤ��Τ餻��������\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}���ǽ���ǧ���줿�͡�$name\t";
                break;
            case "Deny":    // ��ǧ
                $deny_name = $this->getName($this->getUID());    // ��ǧ�Ի�̾
                $attenSubject = "{$to_name} �� ����ֳ���ȿ��� ����ǧ�� ���Τ餻";  // �����ȥ�
                $message  = "{$to_name} ��\n\n";
                $message .= "�ʲ�������ֳ���ȿ����";
                if( $type == "yo" ) $message .= "��������";
                if( $type == "ji" ) $message .= "�Ķȷ�����";
                $message .= "�ˤ�����ǧ����ޤ�����\n\n";
                $message .= "�������{$date}������$deploy\n\n��ȼԡ�$name\n\n";
                $message .= "��ǧ�ԡ�{$deny_name} ��\n\n";
                $message .= "��ǧ��ͳ�ϡ��ʲ����̤�Ǥ���\n\n";
                $message .= "��$memo\n\n";
//$_SESSION['s_sysmsg'] .= "Mail()::���衧{$to_name}����ǧ���줿�͡�$name\t";
                break;
            default:
                break;
        }
        $message .= "�ʾ塣";
        mb_send_mail($to_addres, $attenSubject, $message, $add_head);
    }

    // �Ķȷ����� ���ϴ�λ����
    public function Result($request)
    {
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $query  = "SELECT ji_ad_st FROM over_time_report WHERE date='$date' AND deploy='$deploy' AND yo_ad_rt!='-1'";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // �����
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][0] == "" ) return false;   // �Ķȷ����� �ʤ�
            if( $send > $res[$r][0] ) $send = $res[$r][0];
        }
        switch ($send) {
            case 0:   // ��Ĺ�Ԥ�
                $send = $this->getKatyouUID($deploy);
                break;
            case 1:   // ��Ĺ�Ԥ�
                $send = $this->getButyouUID($deploy);
                break;
            case 2:   // ����Ĺ�Ԥ�
                $send = $this->getKoujyotyouUID();
                break;
            default:
                return false;
        }
        $this->SendMail("Result", $send, $date, $deploy, "", "", "");
    }

    // �Ķȷ����� ���ϴ�λ����
    public function Result2($date, $deploy)
    {
        $query  = "SELECT ji_ad_rt, ji_ad_st FROM over_time_report WHERE date='$date' AND deploy='$deploy' AND yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt>1";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // �����
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][1] == "" ) return false;   // �Ķȷ����� �ʤ�
            if( $res[$r][0]>$res[$r][1] && $send > $res[$r][1] ) $send = $res[$r][1];
        }
        switch ($send) {
            case 0:     // ��Ĺ�Ԥ� �᡼��������ɬ�פʤ�
                return false;
            case 1:     // ��Ĺ�Ԥ�
                $send = $this->getButyouUID($deploy);
                break;
            case 2:     // ����Ĺ�Ԥ�
                $send = $this->getKoujyotyouUID();
                break;
            default:    // �᡼��������ɬ�פʤ�
                return false;
        }
        $this->SendMail("Result", $send, $date, $deploy, "", "", "");
    }

    // ������� ���ϴ�λ����
    public function AfterReport($date, $deploy, $uid)
    {
        $query  = "SELECT ji_ad_st FROM over_time_report WHERE date='$date' AND uid='$uid' AND yo_ad_rt IS NULL";
        $res    = array();
        if( ($rows = $this->getResult2($query, $res)) <= 0 ) {
            return false;
        }
        $send = 3; // �����
        for( $r=0; $r<$rows; $r++ ) {
            if( $res[$r][0] == "" ) return false;   // �Ķȷ����� �ʤ�
            if( $send > $res[$r][0] ) $send = $res[$r][0];
        }
        switch ($send) {
            case 0:   // ��Ĺ�Ԥ�
                $send = $this->getKatyouUID($deploy);
                break;
            case 1:   // ��Ĺ�Ԥ�
                $send = $this->getButyouUID($deploy);
                break;
            case 2:   // ����Ĺ�Ԥ�
                $send = $this->getKoujyotyouUID();
                break;
            default:
                return false;
        }
        $this->SendMail("AfterReport", $send, $date, $deploy, $uid, "", "");
    }

    // ��޽���
    public function Hurry($date, $deploy, $uid)
    {
        $time_hurry = '15:00';                          // �ʹߤϻ�ޥ᡼��
        $now_dt  = new DateTime();                      // ��������
        $hurr_dt = new DateTime("$date $time_hurry");   // �������15:00
        if( $now_dt <= $hurr_dt ) return;   // �̾�
        
        if( $uid == "" ) {
            $no = $this->getPostsNo();
        } else {
            $no = 0;
            if( $this->IsKatyouUID($uid) ) $no = 1;
            if( $this->IsButyouUID($uid) ) $no = 2;
            if( $this->IsKoujyoutyouUID($uid) ) $no = 3;
        }
        
        $type = $memo = "";
        for( ; $no<3; $no++ ) {
            switch ($no) {
                case  3:    // ����Ĺ ���⤷�ʤ�
                    break;
                case  2:    // ��Ĺ �� ����Ĺ�ؤ��Τ餻
                    $send = $this->getKoujyotyouUID();
                    break;
                case  1:    // ��Ĺ �� ��Ĺ�ؤ��Τ餻
                    $send = $this->getButyouUID($deploy);
                    break;
                default:    // ���� �� ��Ĺ�ؤ��Τ餻
                    $send = $this->getKatyouUID($deploy);
                    break;
            }
            if( ! $this->IsAbsence(date('Ymd'), $send) ) break; // �жФ��Ƥ���С��롼�פ�ȴ����
            $type = "absence";
            if($memo) $memo .= " / ";
            $memo .= $this->getName($send);
        }
        
        if( $no>2 ) return; // ���⤷�ʤ���
        
        $this->SendMail("Hurry", $send, $date, $deploy, $uid, $type, $memo);
    }

    // ���ξ�ǧ�Ԥؤ��Τ餻����
    public function NextMaile($date, $deploy)
    {
        $no = $this->getPostsNo();
        
        $type = $memo = "";
        for( ; $no<3; $no++ ) {
            switch ($no) {
                case  3:    // ����Ĺ ���⤷�ʤ�
                    break;
                case  2:    // ��Ĺ �� ����Ĺ�ؤ��Τ餻
                    $send = $this->getKoujyotyouUID();
                    break;
                case  1:    // ��Ĺ �� ��Ĺ�ؤ��Τ餻
                    $send = $this->getButyouUID($deploy);
                    break;
                default:    // ���� �� ��Ĺ�ؤ��Τ餻
                    $send = $this->getKatyouUID($deploy);
                    break;
            }
            if( ! $this->IsAbsence(date('Ymd'), $send) ) break; // �жФ��Ƥ���С��롼�פ�ȴ����
            $type = "absence";
            if($memo) $memo .= " / ";
            $memo .= $this->getName($send);
        }
        
        if( $no>2 ) return; // ���⤷�ʤ���
        
        $this->SendMail("Next", $send, $date, $deploy, "", $type, $memo);
    }

    // ���ä�����
    public function Cancel($request)
    {
        $date   = $request->get('w_date');          // �����
        $type   = $request->get('type');            // type = 'yo' or 'ji'
        $uid    = $request->get('cancel_uid');      // ���ä��оݼ�UID
        $deploy = $request->get('ddlist_bumon');    // ���ä��оݼ�����̾
        $memo   = $request->get('reason');          // ���ä���ͳ
        
        // ����Ĺ ��ǧ�Ѥ� �ʤ���ä��Τ��Τ餻
        if( $this->IsPosAdmit($date, $type, 'ko', $uid) ) {
            $this->SendMail("Cancel", $this->getKoujyotyouUID(), $date, $deploy, $uid, $type, $memo);
        }
        
        // ��Ĺ ��ǧ�Ѥ� �ʤ���ä��Τ��Τ餻
        if( $this->IsPosAdmit($date, $type, 'bu', $uid) ) {
            $this->SendMail("Cancel", $this->getButyouUID($deploy), $date, $deploy, $uid, $type, $memo);
        }
        // ��Ĺ ��ǧ�Ѥ� �ʤ���ä��Τ��Τ餻
        if( $this->IsPosAdmit($date, $type, 'ka', $uid) ) {
            $this->SendMail("Cancel", $this->getKatyouUID($deploy), $date, $deploy, $uid, $type, $memo);
        }
        
        if( $type == 'yo' ) {
            $this->ReportDelete($date, $uid);               // �оݼ� ���
            $no = $request->get('cancel_uno');
            $this->ReportInsert($date, $deploy, $no, $uid); // �оݼ� ������֤��ɲ�
        } else {
            $set   = "ji_str_h=NULL, ji_str_m=NULL, ji_end_h=NULL, ji_end_m=NULL, ji_content=NULL, ji_ad_rt=0, ji_ad_st=NULL, ji_ad_ka=NULL, ji_ad_bu=NULL, ji_ad_ko=NULL";
            $where = "date='$date' AND uid='$uid'";
            $this->ReportUpDate($set, $where); // �Ķȷ�����Τߺ������
        }
    }

    // �ǽ���ǧ�����ʸ��̡�
    public function Notice($date, $deploy, $uid)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // ����Ĺ ��ǧ �� ��Ĺ����Ĺ�ؤ��Τ餻
                if( $this->IsPosAdmit($date, 'yo', 'bu', $uid) ) {
                    $this->SendMail("Notice", $this->getButyouUID($deploy), $date, $deploy, $uid, "", "");
                }
            case  2:    // ��Ĺ   ��ǧ �� ��Ĺ�ؤ��Τ餻
                if( $this->IsPosAdmit($date, 'yo', 'ka', $uid) ) {
                    $this->SendMail("Notice", $this->getKatyouUID($deploy), $date, $deploy, $uid, "", "");
                }
                break;
            default:    // ��Ĺ   ��ǧ �� ���⤷�ʤ���
                break;
        }
    }

    // �ǽ���ǧ�����ʰ���
    public function Notice2($date, $deploy, $name_list)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // ����Ĺ ��ǧ �� ��Ĺ����Ĺ�ؤ��Τ餻
                $this->SendMail("Notice", $this->getButyouUID($deploy), $date, $deploy, $name_list, "", "");
            case  2:    // ��Ĺ   ��ǧ �� ��Ĺ�ؤ��Τ餻
                $this->SendMail("Notice", $this->getKatyouUID($deploy), $date, $deploy, $name_list, "", "");
                break;
            default:    // ��Ĺ   ��ǧ �� ���⤷�ʤ���
                break;
        }
    }

    // ��ǧ�����ʸ��̡�
    public function Deny($type, $date, $deploy, $uid, $memo)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // ����Ĺ ��ǧ �� ��Ĺ����Ĺ�ؤ��Τ餻
                if( $this->IsPosAdmit($date, $type, 'bu', $uid) ) {
                    $this->SendMail("Deny", $this->getButyouUID($deploy), $date, $deploy, $uid, $type, $memo);
                }
            case  2:    // ��Ĺ   ��ǧ �� ��Ĺ�ؤ��Τ餻
                if( $this->IsPosAdmit($date, $type, 'ka', $uid) ) {
                    $this->SendMail("Deny", $this->getKatyouUID($deploy), $date, $deploy, $uid, $type, $memo);
                }
                break;
            default:    // ��Ĺ   ��ǧ �� ���⤷�ʤ���
                break;
        }
    }

    // ��ǧ�����ʰ���
    public function Deny2($type, $date, $deploy, $name_list, $memo)
    {
        $no = $this->getPostsNo();
        switch ($no) {
            case  3:    // ����Ĺ ��ǧ �� ��Ĺ����Ĺ�ؤ��Τ餻
                $this->SendMail("Deny", $this->getButyouUID($deploy), $date, $deploy, $name_list, $type, $memo);
            case  2:    // ��Ĺ   ��ǧ �� ��Ĺ�ؤ��Τ餻
                $this->SendMail("Deny", $this->getKatyouUID($deploy), $date, $deploy, $name_list, $type, $memo);
                break;
            default:    // ��Ĺ   ��ǧ �� ���⤷�ʤ���
                break;
        }
    }

// ============================================================================
// ���� =======================================================================
// ============================================================================
    // ������桼����ID ����
    public function getUID()
    {
        return $this->uid;
    }
    
    // ǯ�����Υɥ�åץ�����ꥹ�Ⱥ���
    public function getSelectOptionDate($start, $end, $def)
    {
        for ($i = $start; $i <= $end ; $i++) {
            if ($i == $def) {
                echo "<option value='" . sprintf("%02d", $i) . "' selected>" . $i . "</option>";
            } else {
                echo "<option value='" . sprintf("%02d", $i) . "'>" . $i . "</option>";
            }
        }
    }
    
    // �����ǽ������Υɥ�åץ�����ꥹ�Ⱥ���
    public function setSelectOptionBumon($request)
    {
        $b_name = $this->getBumonName();   // ����̾����
        array_unshift($b_name, "---- ���򤷤Ʋ����� ----");
        
        $max = count($b_name);
        for( $i = 0; $i < $max ; $i++ ) {
            if( $this->IsDisp($i) ) {
                if( $request->get('ddlist_bumon') == $b_name[$i] ) {
                    echo "<option value='{$b_name[$i]}' selected>{$b_name[$i]}</option>";
                } else {
                    echo "<option value='{$b_name[$i]}'>{$b_name[$i]}</option>";
                }
            }
        }
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
    
    // �����Ǥ�����
    public function IsHoliday($date)
    {
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
        return true;
    }
    
    // ����ǯ�����Ȥ������������
    public function getTargetDateDay($target_date, $cap)
    {
        $week = array(' (��)',' (��)',' (��)',' (��)',' (��)',' (��)',' (��)');
        
        $day_no = date('w', strtotime($target_date));
        
        if( $cap != 'on') {
            if( $day_no == 0 ) {            // �������ʿ����֡�
                return $target_date . "<font color='red'>$week[$day_no]</font>";
            } else if( $day_no == 6 ) {     // �������ʿ����ġ�
                return $target_date . "<font color='blue'>$week[$day_no]</font>";
            } else if( $this->IsHoliday($target_date) ) {  // ��ҥ������������ʿ����֡�
                return $target_date . "<font color='red'>$week[$day_no]</font>";
            } else {
                return $target_date . $week[$day_no];         // ����¾ ʿ�� �Ķ����ʿ����ǥե���ȹ���
            }
        } else {
            return $target_date . $week[$day_no];   // �����ο����ѹ������֤���
        }
    }
    
    // �Ұ�̾����
    public function getName($str)
    {
        $query = "SELECT name FROM user_detailes WHERE uid='$str'";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return '';
        }
        return trim($res[0][0]);
    }

    // ����̾���֤�
    public function getPostsName()
    {
        return $this->posts_na;
    }

    // ����No���֤� 1 or 2 or 3
    public function getPostsNo()
    {
        return $this->posts_no;
    }

    // ��ǧ���������
    public function getAdmitStatus($root, $no)
    {
        if( $no == "" ) return "----";
        if( $root != '-1' && ($root == $no || $root < $no) ) return "��ǧ ��";// return "��ǧ ��λ";

        $status = "";
        switch ($no) {
            case  0:    // ��Ĺ ��ǧ�Ԥ�
                $status = "<font style='background-color:Cyan;'>��Ĺ ��ǧ�Ԥ�</font>";
                break;
            case  1:    // ��Ĺ ��ǧ�Ԥ�
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>��Ĺ ��ǧ</font>";
                } else {
                    $status = "<font style='background-color:Lime;'>��Ĺ ��ǧ�Ԥ�</font>";
                }
                break;
            case  2:    // ����Ĺ ��ǧ�Ԥ�
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>��Ĺ ��ǧ</font>";
                } else {
                    $status = "<font style='background-color:GhostWhite;'>����Ĺ ��ǧ�Ԥ�</font>";
                }
                break;
            case  3:    // ��ǧ ��λ
                if( $root == "-1" ) {
                    $status = "<font style='color:red;'>����Ĺ ��ǧ</font>";
                } else {
                    $status = "��ǧ ��";
                }
//                $status = "��ǧ ��λ";
                break;
            default:
                $status = "----";
                break;
        }
        return $status;
    }

// ============================================================================
// ���� =======================================================================
// ============================================================================
    // ����Υɥ�åץ�����ꥹ�Ⱥ���
    public function setSelectOptionTime($start, $end, $def)
    {
        echo "<option value='-1'>--</option>";
        for ($i = $start; $i <= $end ; $i++) {
            if ($i == $def) {
                echo "<option value='" . sprintf("%02s",$i) . "' selected>" . $i . "</option>";
            } else {
                if( $end == 23 ) {
                    echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                }
                if( $end == 59 ) {
                    if( $i == 0 || $i%5 == 0 ) {
                        echo "<option value='" . sprintf("%02s",$i) . "'>" . $i . "</option>";
                    }
                }
            }
        }
    }
    
    // �������դ������ɽ���ѥǡ�������
    public function getViewData($day, $deploy, &$field, &$res)
    {
        $query = "SELECT * FROM over_time_report WHERE date='$day' AND deploy='$deploy' ORDER BY no";
        $res = $field = array();
        return getResultWithField2( $query, $field, $res );
    }

    // ��������λ�̾�����
    public function GetNameList($bumon, &$res)
    {
        $rows = 0;
        $where = $this->getBumonActID($bumon);
        if( $where != '' ) {
            $where = "WHERE " . $where . " AND ud.retire_date IS NULL ";
            if( $bumon == "���������� �ײ衦���㷸" ) {
                $order = "ORDER BY ud.pid DESC, ud.sid DESC, ud.uid ASC";
            } else {
                $order = "ORDER BY ud.sid DESC, ud.pid DESC, ud.uid ASC";   // �̾�
            }
            $query = "SELECT ud.uid, ud.name FROM user_detailes AS ud LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) $where $order";
//            $_SESSION['s_sysmsg'] .= 'GetNameList()::' . $query;
            $res = array();
            if ( ($rows=getResultWithField2($query, $field, $res)) <= 0 ) {
                ; //$_SESSION['s_sysmsg'] .= '��Ͽ������ޤ���';
            } else {
                ; //$_SESSION['s_sysmsg'] .= $rows . '�濫��ޤ���';
            }

        }
        return $rows;
    }

    // ��������λ�̾�����
    public function NameListCheck($date, &$res, $max)
    {
        for( $i=0; $i<$max; $i++ ) {
            $uid = trim($res[$i][0]);
            $query = "SELECT deploy FROM over_time_report WHERE date='$date' AND uid='$uid'";
            $w_res = array();
            if( getResult2($query, $w_res) > 0 ) {
                unset($res[$i]);
            }
        }
        $res = array_values($res);
        return count($res);
    }

    // �������ա��桼�����ɲ�
    public function ReportInsert($date, $deploy, $no, $uid)
    {
        $columns = " date,    deploy,     no,    uid";
        $values  = "'$date', '$deploy', '$no', '$uid'";
        $insert_qry = "INSERT INTO over_time_report ($columns) VALUES ($values);";
        return query_affected($insert_qry);
    }

    // ������󡦾��ǹ���
    public function ReportUpDate($set, $where)
    {
        $update_qry = "UPDATE over_time_report SET $set WHERE $where";
        return query_affected($update_qry);
    }

    // �������ա��桼�������
    public function ReportDelete($date, $uid)
    {
        $delete_qry = "DELETE FROM over_time_report WHERE date='$date' AND uid='$uid'";
        return query_affected($delete_qry);
    }

    // ������Ͽ����Ƥ���ǡ������ɤ߹��߻��Υǡ��������
    public function ReportDiff($date, $uid, $r, $request)
    {
        $query = "SELECT * FROM over_time_report WHERE date='$date' AND uid='$uid'";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        
        $fiels = $request->get('fiels');
        for( $f=0; $f<$fiels; $f++ ) {
            if( $f==14 || $f==15 ) continue;  // ����Ĺ�����Ȥϥ����åס�
            $data = $request->get("res{$r}_{$f}");
            if( $res[0][$f] != $data ) {
                return false;
            }
        }
        return true;
    }

    // �ɤ߹��߻��Υǡ������������ϥǡ�������� �������Ƥ����Ǥ�����
    public function IsDataUp($request, $r)
    {
        if( $request->get('ddlist_y_s_h' . $r) ) {  // ͽ��� ���� �� �����
            if( $request->get("res{$r}_9") == '-1' ) return true; // ���� ��ǧ
            if( ($b = $request->get("ddlist_y_s_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_4") != $b ) return true;
            if( ($b = $request->get("ddlist_y_s_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_5") != $b ) return true;
            if( ($b = $request->get("ddlist_y_e_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_6") != $b ) return true;
            if( ($b = $request->get("ddlist_y_e_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_7") != $b ) return true;
            if( $request->get("res{$r}_8") != $request->get("z_j_r{$r}") ) return true;
        } else if( $request->get('ddlist_j_s_h' . $r) ) {  // ���Ӥ� ���� �� �����
            if( $request->get("res{$r}_22") == '-1' ) return true; // ���� ��ǧ
            if( ($b = $request->get("ddlist_j_s_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_16") != $b ) return true;
            if( ($b = $request->get("ddlist_j_s_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_17") != $b ) return true;
            if( ($b = $request->get("ddlist_j_e_h{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_18") != $b ) return true;
            if( ($b = $request->get("ddlist_j_e_m{$r}"))=='-1' ) $b = "";
            if( $request->get("res{$r}_19") != $b ) return true;
            if( $request->get("res{$r}_20") != $request->get("j_g_n{$r}") ) return true;
        }
        return false;
    }

    // ���ֳ� ���� �쥳���ɹ���
    public function ReportRenewal($request, $type, $no)
    {
        if( $type == 'yo' ) {
            $flag = $request->get('ddlist_y_s_h' . $no);     // ͽ��� ���� �� �����
        } else if( $type == 'ji' ) {
            $flag = $request->get('ddlist_j_s_h' . $no);     // ���Ӥ� ���� �� �����
        } else {
            $_SESSION['s_sysmsg'] .= '�쥳���ɹ��� �Բ� type=[' . $type . ']';
            return;
        }

        if( $flag != '' ) { // ���֤Υɥ�åץ�����ꥹ��ͭ���Ԥʤ��̤�
            $date   = $request->get('w_date');
            $deploy = $request->get('ddlist_bumon');
            $uid    = $request->get('uid' . $no);
            $where  = "date='$date' AND uid='$uid'";
            if( $flag == -1 ) { // ���ϻ� ����ʤ�
                // ��¸�ǡ����Υ��ꥢ
                $this->TimeInfoClear($type, $where);            // ���־���
                $this->ConttentInfoClear($type, $no, $where);   // ���ơ�����
                $this->AdmitInfoClear($type, $where);           // ��ǧ����
            } else {
                // ���򤷤� ���� �� ʬ ��λ �� ʬ �� �ѿ��إ��å�
                $this->setTimeInfo($request, $type, $no);
                $this->TimeInfoUpDate($type, $where);                   // ���־��󹹿�
                $this->ConttentInfoUpDate($request, $type, $no, $where);// ���ơ����͹���
                $this->AdmitInfoUpDate($request, $type, $uid, $where);  // ��ǧ���󹹿�
                if( $type=='yo') {
                    $this->Hurry($date, $deploy, $uid); // ���
                } else {
                    $this->AfterReport($date, $deploy, $uid); // ����
                }
            }
        }
    }

    // ���ֳ� ���� ���־������
    public function getTimeInfo(&$s_h, &$s_m, &$e_h, &$e_m)
    {
        $s_h = $this->str_hour; $s_m = $this->str_min;
        $e_h = $this->end_hour; $e_m = $this->end_min;
    }

    // ���ֳ� ���� ���־������ $type = 'yo' or 'ji'
    public function setTimeInfo($request, $type, $no)
    {
        if( $type == 'yo' ) {
            $str = 'y';
        } else if( $type == 'ji' ) {
            $str = 'j';
        } else {
            $_SESSION['s_sysmsg'] .= '���־��󥻥å� �Բ� type=[' . $type . ']';
            return;
        }

        $str_h_name = 'ddlist_' . $str . '_s_h'; $str_m_name = 'ddlist_' . $str . '_s_m';
        $end_h_name = 'ddlist_' . $str . '_e_h'; $end_m_name = 'ddlist_' . $str . '_e_m';

        $this->str_hour = $request->get($str_h_name . $no);
        $this->str_min  = $request->get($str_m_name . $no);
        $this->end_hour = $request->get($end_h_name . $no);
        $this->end_min  = $request->get($end_m_name . $no);
    }

    // ���ֳ� ���� ���־��󥯥ꥢ $type = 'yo' or 'ji'
    public function TimeInfoClear($type, $where)
    {
        $set = "{$type}_str_h=NULL, {$type}_str_m=NULL, {$type}_end_h=NULL, {$type}_end_m=NULL";

        $this->ReportUpDate($set, $where);
    }

    // ���ֳ� ���� ���־��󹹿� $type = 'yo' or 'ji'
    public function TimeInfoUpDate($type, $where)
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);

        $set = "{$type}_str_h='$s_h', {$type}_str_m='$s_m', {$type}_end_h='$e_h', {$type}_end_m='$e_m'";

        $this->ReportUpDate($set, $where);
    }

    // ���ֳ� ���� ���ơ����ͥ��ꥢ $type = 'yo' or 'ji'
    public function ConttentInfoClear($type, $no, $where)
    {
        $set = "{$type}_content=NULL, ji_remarks=NULL";

        $this->ReportUpDate($set, $where);
    }

    // ���ֳ� ���� ���ơ����͹��� $type = 'yo' or 'ji'
    public function ConttentInfoUpDate($request, $type, $no, $where)
    {
        if( $type == 'yo' ) {
            $content_name = 'z_j_r';
        } else if( $type == 'ji' ) {
            $content_name = 'j_g_n';
        } else {
            $_SESSION['s_sysmsg'] .= '���ơ����͹��� �Բ� type=[' . $type . ']';
            return;
        }

        $content = $request->get($content_name . $no);
        $set = "{$type}_content='$content'";
        if( ($bikou = $request->get('bikou' . $no)) != "" ) {
            $set .= ", ji_remarks='$bikou'";
        }
        $this->ReportUpDate($set, $where);
    }

    // �����ˤ�뾵ǧ�롼��
    public function GetDayAdmitRoot($date)
    {
        $day_no = date('w', strtotime($date));
        if( $this->IsHoliday($date) ) {                // ��ҥ�����������
            $root = 3;
        } else if ( $day_no == 3 || $day_no == 5 ) {    // �塢�� ����
            $root = 3;
            if($this->IsProlong()) $root = 1;
        } else {    // ����¾ ��С��� ����
            $root = -1;
        }
        return $root;
    }

    // ��Ĺ�Ǥ�����
    public function IsProlong()
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);
        // �����
        $diffTime = array();
        // �����ॹ����פκ���׻�
        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime('17:30');
        if($difSeconds<=0) return true; // 17:15 �ޤǤϱ�Ĺ
        return false;
    }

    // ���֤ˤ�뾵ǧ�롼��
    public function GetTimeAdmitRoot()
    {
        $this->getTimeInfo($s_h, $s_m, $e_h, $e_m);
//        $_SESSION['s_sysmsg'] .= 'GetTimeAdmitRoot()::' . $s_h . ':' . $s_m . ' - ' . $e_h . ':' . $e_m;
        // �����
        $diffTime = array();
        // �����ॹ����פκ���׻�
//        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime($s_h . ':' . $s_m);
        $difSeconds = strtotime($e_h . ':' . $e_m) - strtotime('17:30');    // 17:30�ʹߤ��ĶȰ���
        // ʬ�κ������
        $difMinutes = $difSeconds / 60;
        $diffTime['minutes'] = $difMinutes % 60;
        // ���κ������
        $difHours = ($difMinutes - ($difMinutes % 60)) / 60;
        $diffTime['hours'] = $difHours;
//        $_SESSION['s_sysmsg'] .= 'GetTimeAdmitRoot()::' . $diffTime['hours'] . ':' . $diffTime['minutes'];
        // 1���֤ޤǤʤ�롼��1
        if( $diffTime['hours'] < 1 ) return 1;
        if( $diffTime['hours'] == 1  && $diffTime['minutes'] == 0) return 1;
        
        // 1���֤�Ķ����ʤ�롼��2
        return 2;
    }

    // ��ǧ�롼�ȼ���
    public function GetAdmitRoot($date)
    {
        $root = $this->GetDayAdmitRoot($date);
        if( $root < 0 ) {
            if($this->IsProlong()) {
                $root = 1;
            } else {
                $root = $this->GetTimeAdmitRoot();
            }
        }
        return $root;
    }

    // ���ֳ� ���� ��ǧ���󥯥ꥢ $type = 'yo' or 'ji'
    public function AdmitInfoClear($type, $where)
    {
        if( $type == 'yo' ) {
            $set = "{$type}_ad_rt=NULL, {$type}_ad_st=NULL, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko=NULL, ji_ad_rt=NULL";
        } else if( $type == 'ji' ) {
            $set = "{$type}_ad_rt=NULL, {$type}_ad_st=NULL, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko=NULL";
        } else {
            $_SESSION['s_sysmsg'] .= '��ǧ���󥯥ꥢ �Բ� type=[' . $type . ']';
            return;
        }
        $this->ReportUpDate($set, $where);
    }

    // ���ֳ� ���� ��ǧ���󹹿� $type = 'yo' or 'ji'
    public function AdmitInfoUpDate($request, $type, $uid, $where)
    {
        $date = $request->get('w_date');
        
        $root = $this->GetAdmitRoot($date);
        
        $set = "{$type}_ad_rt='$root', {$type}_ad_st=0";
        switch ($root) {
            case 1:     // ��Ĺ�ξ�ǧ��ɬ��
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu=NULL, {$type}_ad_ko=NULL";
                break;
            case 2:     // ��Ĺ����Ĺ�ξ�ǧ��ɬ��
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu='m', {$type}_ad_ko=NULL";
                break;
            case 3:     // ��Ĺ����Ĺ������Ĺ�ξ�ǧ��ɬ��
                $set .= ", {$type}_ad_ka='m', {$type}_ad_bu='m', {$type}_ad_ko='m'";
                break;
            default:    // 
                $_SESSION['s_sysmsg'] .= '��ǧ���󹹿� �Բ� root=[' . $root . ']';
                return;
        }

        if( $this->IsKoujyoutyouUID($uid) ) {
            $set = "{$type}_ad_rt='3', {$type}_ad_st=2, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko='m'";
        } else if( $this->IsButyouUID($uid) ) {
            $set = "{$type}_ad_rt='3', {$type}_ad_st=2, {$type}_ad_ka=NULL, {$type}_ad_bu=NULL, {$type}_ad_ko='m'";
        } else if( $this->IsKatyouUID($uid) ) {
            if( $root == 3 ) {
                $set = "{$type}_ad_rt='3', {$type}_ad_st=1, {$type}_ad_ka=NULL, {$type}_ad_bu='m', {$type}_ad_ko='m'";
            } else {
                $set = "{$type}_ad_rt='2', {$type}_ad_st=1, {$type}_ad_ka=NULL, {$type}_ad_bu='m', {$type}_ad_ko=NULL";
            }
        }
        
        $this->ReportUpDate($set, $where);
    }

    // �������ա�����λ��ֳ� ���� �Ϥ���ޤ�����
    public function IsReport($request)
    {
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        
        $query = "SELECT date FROM over_time_report WHERE date='$date' AND deploy='$deploy' LIMIT 1";
        if( getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // ���ֳ� ���� ��ݡ��Ⱥ���
    public function ReportCreate($request)
    {
        if( $request->get('appli') == '' ) return true; // ���ϲ��⤷�ʤ���
        
        if( $request->get('v_data') ) return true; // ����ɽ��������ݡ��Ȥ������Ǥ��Ƥ�ʤ�������ʤ���
        
        if( $this->IsReport($request) ) {    // ���ߥǡ����١�����ˡ���ݡ��Ȥ�¸�ߤ�����
            $_SESSION['s_sysmsg'] = "¾�����������˥ǡ�����������Ƥ��ޤ������ɹ���ľ���ޤ���";
            return false;
        }
        
        $date   = $request->get('w_date');          // ���ꤷ�������
        $deploy = $request->get('ddlist_bumon');    // ���ꤷ������
        $max    = $request->get('rows');            // ��������Ͽ�쥳���ɿ�
        for( $r=0; $r<$max; $r++ ) {
            $uid = $request->get('uid' . $r);
            $this->ReportInsert($date, $deploy, $r+1, $uid);    // ���ܾ��󿷵���Ͽ
        }
        
        return true;
    }

    // ���ֳ� ���� �ơ��֥����
    public function AppliUp($request)
    {
        if( $request->get('appli') != 'up' ) return false;  // [��Ͽ]�ܥ���Υ���å��ʳ���ȴ���롣
        
        $max = $request->get('rows'); // ����Υ쥳���ɿ�
        $date = $request->get('w_date');
        $name = "";
        $up = false;
        for( $r=0; $r<$max; $r++ ) {
            $uid = $request->get('uid' . $r);
            if( ! $this->ReportDiff($date, $uid, $r, $request) ) {    // ����ɽ���ǡ����Ⱥ��٥ǡ������ɹ������
                $name .= $this->getName($uid) . " / ";
                continue;
            }
            if( ! $this->IsDataUp($request, $r) ) { // ������ǽ�����ʻ��֡����Ƥ��ѹ������뤫����
                continue;
            }
            
            $this->ReportRenewal($request, 'yo', $r);  // ���ֳ� ���� �쥳���� �������� ����
            
            $this->ReportRenewal($request, 'ji', $r);  // ���ֳ� ���� �쥳���� �Ķȷ����� ����
            
            $up = true; // �쥳���ɹ�����λ�ե饰
        }
        if( $name ) {
            $_SESSION['s_sysmsg'] .= "$name �Υǡ����ϡ���Ͽ�Ǥ��ޤ���Ǥ��������ɹ���ľ���ޤ���";
            return false;
        }
        if( ! $up ) {
            $_SESSION['s_sysmsg'] .= "��Ͽ�Ǥ���ǡ����Ϥ���ޤ���Ǥ�����";
            return false;
        }
        
        $_SESSION['s_sysmsg'] .= '�ǡ�������Ͽ����λ���ޤ�����';
        
        if( $this->Result($request) ) {
            echo "����������ƴ�λ";
        }
        return true;
    }

    // ���ֳ� ���� �ơ��֥���ɲ�
    public function AppliAdd($request)
    {
        if( $request->get('appli') != 'add' ) return false; // [�ɲ�]�ܥ���Υ���å��ʳ���ȴ���롣
        
        $add_uid = $request->get('add_uid');
        if( ! $this->IsSyain($add_uid) ) {   // TNK�Ұ���Ƚ��
            $_SESSION['s_sysmsg'] .= "[$add_uid] �ϼҰ��Ǥʤ��١���Ͽ�Ǥ��ޤ���";
            return false;
        }
        
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $name   = trim($this->getName($add_uid));
        
        $query = "SELECT deploy FROM over_time_report WHERE date='$date' AND uid='$add_uid'";
        if( getResult2($query, $res) > 0 ) {
            $this->ReportDelete($date, $add_uid);
            $_SESSION['s_sysmsg'] = "$name �ͤ� {$res[0][0]} ��� �����";
        }

        $query = "SELECT no FROM over_time_report WHERE date='$date' AND deploy='$deploy' ORDER BY no DESC LIMIT 1";
        if( getResult2($query, $res) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$name �ͤ��ɲä˼��Ԥ��ޤ�����";
            return false;
        }
        $no = $res[0][0] + 1;
        $this->ReportInsert($date, $deploy, $no, $add_uid);    // ���ܾ��󿷵���Ͽ
        $_SESSION['s_sysmsg'] .= "$name �ͤ��ɲä��������ޤ�����";
        return true;
    }

    // ���ֳ� ���� ��Ĺ����Ĺ�����ȹ���
    public function UpComment($request)
    {
        if( $request->get('appli') == '' ) return true; // ���ʤ�ȴ���롣
        
        $set = "";
        if( $com_ka = trim($request->get('comment_ka')) ) {
            $set .= "comment_ka='$com_ka'";
        } else {
            $set .= "comment_ka=NULL";
        }
        if( $set ) $set .= ", ";
        if( $com_bu = trim($request->get('comment_bu')) ) {
            $set .= "comment_bu='$com_bu'";
        } else {
            $set .= "comment_bu=NULL";
        }
        $date   = $request->get('w_date');
        $deploy = $request->get('ddlist_bumon');
        $where = "date='$date' AND deploy='$deploy'";
        
        // �񤭹������ˡ��ɤ߹��߻��ȸ���DB����Ӥ��ѹ�����Ƥ��ʤ����Ȥ��ǧ����
        $query = "SELECT comment_ka, comment_bu, no FROM over_time_report WHERE $where ORDER BY no LIMIT 1";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        if($request->get("res0_14")!=$res[0][0] || $request->get("res0_15")!=$res[0][1]) {
            if( $request->get('appli') == 'comment' ) {
                $_SESSION['s_sysmsg'] .= "����Ĺ�Υ����Ȥ��̤ν�ǹ�������Ƥ���١������Ǥ��ޤ���Ǥ�����";
                return false;
            }
        }
        
        $this->ReportUpDate($set, $where);
        if( $request->get('appli') == 'comment' ) {
            $_SESSION['s_sysmsg'] .= "����Ĺ�Υ����Ȥ򹹿����ޤ�����";
        }
        return true;
    }

    // �����Ĺ�˾�ǧ����Ƥ��ޤ����� type= 'yo' or 'ji' / pos = 'ka' or 'bu' or 'ko'
    public function IsPosAdmit($date, $type, $pos, $uid)
    {
        $column = $type . '_ad_' . $pos;

        $query = "SELECT $column FROM over_time_report WHERE date='$date' AND uid='$uid' AND $column='s' LIMIT 1";

        if( getResult2($query, $res) <= 0 ) return false;
        return true;
    }

    // ̤��ǧ�Ϥ���ޤ����� type= 'yo' or 'ji'
    public function IsNoAdmit($type, $date, $uid)
    {
        $column1 = $type . '_ad_rt';
        $column2 = $type . '_ad_ka';
        $column3 = $type . '_ad_bu';
        $column4 = $type . '_ad_ko';

//        $query = "SELECT date FROM over_time_report WHERE date='$date' AND uid='$uid' AND $column1!='-1' AND ($column2='m' OR $column3='m' OR $column4='m')";
        $query = "SELECT date FROM over_time_report WHERE date='$date' AND uid='$uid' AND ($column1='-1' OR $column2='m' OR $column3='m' OR $column4='m')";
        if( getResult2($query, $res) <= 0 ) return false;
        return true;
    }

    // �����ʷ�̡ˤξ��֤����
    public function getApplStatus($type, $view, $res, $idx)
    {
        if( $type == 'yo' ) {
            if( ! $view || $res[$idx][9] == "" || $res[$idx][9] == "0" || $res[$idx][10] == "0" || ($res[$idx][10] == "1" && $res[$idx][11] == "") || ($res[$idx][10] == "2" && $res[$idx][12] == "") ) {
                $status = '�ݡ�';
            } else if( $res[$idx][9] == "-1" ) {
                $status = "��ǧ";
            } else if( $res[$idx][9] <= $res[$idx][10] ) {
                $status = "��λ";
            } else {
                $status = "����";
            }
        } else {
            if( ! $view || $res[$idx][22] == "" || $res[$idx][22] == "0" || $res[$idx][23] == "0" || ($res[$idx][23] == "1" && $res[$idx][24] == "") || ($res[$idx][23] == "2" && $res[$idx][25] == "") ) {
                $status = '�ݡ�';
            } else if( $res[$idx][22] == "-1" ) {
                $status = "��ǧ";
            } else if( $res[$idx][22] <= $res[$idx][23] ) {
                $status = "��λ";
            } else {
                $status = "����";
            }
        }
        
        return $status;
    }

// ============================================================================
// ��ǧ =======================================================================
// ============================================================================
    // ��������̾����
    public function getWhereDeploy()
    {
        if( $this->IsMaster() ) return "(deploy IS NOT NULL)";

        switch ($this->act_id) {
            case 600:   // ����Ĺ
                if( $this->uid == '012394' ) {
                    return "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
                }
                return "(deploy IS NOT NULL)";
            case 610:   // ������
                return "(deploy='��̳��' OR deploy='���ʴ�����')";
            case 605:   // �ɣӣϻ�̳��
            case 650:   // ������ ��̳��
            case 651:   // ������ ��̳�� ��̳
            case 660:   // ������ ��̳�� ��̳
                return "(deploy='��̳��')";
            case 670:   // ������ ���ʴ�����
                return "(deploy='���ʴ�����')";
            case 501:   // ������
                return "(deploy='�ʼ��ݾڲ�' OR deploy='���Ѳ�')";
            case 174:   // ������ �ʼ�������
            case 517:   // ������ �ʼ������� ���ץ鸡��ô��
            case 537:   // ������ �ʼ������� ���ץ鸡��ô��
            case 581:   // ������ �ʼ������� ���ץ鸡��ô��
                return "(deploy='�ʼ��ݾڲ�')";
            case 173:   // ������ ���Ѳ�
            case 515:   // ������ ���Ѳ�
            case 535:   // ������ ���Ѳ�
                return "(deploy='���Ѳ�')";
            case 582:   // ��¤��
                return "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
            case 518:   // ��¤�� ��¤����
            case 519:   // ��¤�� ��¤����
            case 556:   // ��¤�� ��¤����
            case 520:   // ��¤�� ��¤����
                return "(deploy='��¤�� ��¤����')";
            case 547:   // ��¤�� ��¤����
            case 528:   // ��¤�� ��¤����
            case 527:   // ��¤�� ��¤����
                return "(deploy='��¤�� ��¤����')";
            case 500:   // ������
                return "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸' OR deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����' OR deploy='��˥���Ω��')";
            case 545:   // ������ ����������
            case 512:   // ������ ���������� �ײ跸 ��ô��
            case 532:   // ������ ���������� �ײ跸 ��ô��
            case 513:   // ������ ���������� ���㷸 ��ô��
            case 533:   // ������ ���������� ���㷸 ��ô��
            case 514:   // ������ ���������� ��෸ ���ץ���
            case 534:   // ������ ���������� ��෸ ��˥����
                return "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸')";
            case 176:   // ������ ���ץ���Ω��
            case 522:   // ������ ���ץ���ΩMAô��
            case 523:   // ������ ���ץ���ΩHAô��
            case 525:   // ������ ���ץ�����ô��
                return "(deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����')";
            case 551:   // ������ ��˥���Ω��
            case 175:   // ������ ��˥���Ωô��
            case 572:   // ������ �ԥ��ȥ���ô��
                return "(deploy='��˥���Ω��')";
            default:
                return "(deploy IS NULL)";
        }
    }

    // �����������̾�����
    public function GetDeployName($where, &$res)
    {
        $query = "SELECT DISTINCT deploy FROM over_time_report WHERE $where ORDER BY deploy";
        
        $res= array();
        
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        
        return $rows;
    }

    // ����������դ�����̾�����
    public function GetDateDeploy($where, &$res)
    {
        $query = "SELECT DISTINCT date, deploy FROM over_time_report WHERE $where ORDER BY date, deploy";
        
        $res= array();
        
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        
        return $rows;
    }

    // ��ǧ��������������NULL=���ס�'s'=��ǧ��'h'=��ǧ��'m'=̤��ǧ��'f'=�Ժ�
    public function GetAdmitInfo($flag)
    {
        if( $flag == 's' ) {
            return "<font color='red'>OK</font>";
        } else if( $flag == 'h' ) {
            return "<font color='red'>��ǧ</font>";
        } else if( $flag == 'm' ) {
            return '̤';
        } else if( $flag == 'f' ) {
            return "<font color='red'>�Ժ�</font>";
        } else {
            return '----';
        }
    }

    // ������ο���쥳���ɤ����
    public function GetReport($where, &$res)
    {
        $query = "SELECT * FROM over_time_report WHERE $where ORDER BY date, deploy, no";
        if( ($rows=getResult2($query, $res)) <= 0 ) return -1;
        return $rows;
    }

    // ��Ĺ����Ĺ���� �Ǥ�����
    public function IsKatyou()
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$this->uid' AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return true;
    }

    // ��Ĺ����Ĺ���� �桼����ID�Ǥ�����
    public function IsKatyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return true;
    }

    // ��Ĺ����Ĺ���� �Ǥ�������95=������Ĺ��
    public function IsButyou()
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$this->uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // ��Ĺ����Ĺ���� �桼����ID�Ǥ�������95=������Ĺ��
    public function IsButyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
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
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // ����Ĺ �桼����ID�Ǥ�����
    public function IsKoujyoutyouUID($uid)
    {
        $query = "
                    SELECT          ct.act_id
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ud.uid = '$uid' AND ud.pid=110
                 ";
        $res = array();
        if ( $this->getResult2($query, $res) <= 0 ) return false;
        
        return true;
    }

    // ��ǧ����򹹿�
    public function AdmitUp($request)
    {
        $max     = $request->get('rows_max');
        $column  = $request->get('column');             // xx_ad_
        $column1 = $column . $request->get('posts');    // xx_ad_xx
        $column2 = $column . 'st';                      // xx_ad_st
        $column3 = $column . 'rt';                      // xx_ad_rt
        $pos_no  = $this->getPostsNo();

        for( $i=0; $i<$max; $i++ ) {
            if( $request->get('radio_yo' . $i) ) {
                $type = 'yo';
            } else if( $request->get('radio_ji' . $i) ) {
                $type = 'ji';
            } else {
                continue;
            }
            
            $flag   = $request->get('radio_' . $type . $i);
            $date   = $request->get('w_date' . $i);
            $deploy = $request->get('deploy' . $i);
            
            if( $flag ) {   // ��ǧ or ��ǧ ���򤵤�Ƥ���
                $rows = $request->get('rows' . $i);
                $up_flag = false;       // �����ե饰
                $hurry_maile = false;   // ��ޥե饰
                $next_maile = false;    // ���οͤإ᡼�������ե饰
                $deny_maile = false;    // ��ǧ�ե饰
                $notice_maile = false;  // �ǽ���ǧ�ե饰
                $name_list = "";        // ��̾�ꥹ��
                for( $r=0; $r<$rows; $r++ ) {
                    $up   = $request->get('up'  . $i . '_' . $r);
                    if( $up != 'on' ) continue; // �����������ʤ��ͤϥ����åס�
                    $uid  = $request->get('uid' . $i . '_' . $r);
                    $root = $request->get($type . '_root' . $i . '_' . $r);
                    $set  = "$column1='$flag', $column2=$pos_no";
                    // �Ժ߻�
                    $absence_ka = $request->get('absence_ka'  . $i . '_' . $r);
                    $absence_bu = $request->get('absence_bu'  . $i . '_' . $r);
                    if( $absence_ka == "on" ) { // �Ժ߻���ǧ
                        $set .= ", $column" . "ka" . "='f'";
                    }
                    if( $absence_bu == "on" ) { // �Ժ߻���ǧ
                        $set .= ", $column" . "bu" . "='f'";
                    }
                    if( $flag == 'h' ) {    // ��ǧ
                        $set .= ", {$type}_ad_rt=-1";
                    } else {
                        if( $type == 'yo' ) {
                            if( $pos_no >= $root ) {
                                $set .= ", ji_ad_rt=0";
                            } else {
                                $hurry_maile = true;
                            }
                        }
                        if( $pos_no < $root ) {
                            $next_maile = true; // ���ξ�ǧ�Ԥ�����ʤ�᡼�������ե饰ON
                        }
                    }
                    
                    $where = "date='$date' AND uid='$uid' AND $column3='$root'";
                    if( $absence_ka || $absence_bu ) { // �Ժ߻���ǧ
                        if( $pos_no == 2) {
                            $where = "date='$date' AND uid='$uid' AND $column" . "ka" . "='m'";
                        } else { // $pos_no == 3
                            $where = "date='$date' AND uid='$uid' AND ($column" . "bu" . "='m' OR $column" . "ka" . "='m')";
                        }
                    }
                    
                    if( $this->ReportUpDate($set, $where) <= 0) {
                        $name = $this->getName($uid);
                        $_SESSION['s_sysmsg'] .= "$name �ο����ϼ��ä��줿��ǽ��������ޤ���";
                    } else {
                        if( $flag == 'h' ) {    // ��ǧ
                            // ��ǧ�������Ȥ�ǧ�ԡʲ�Ĺ����Ĺ�ˤؤ��Τ餻�������
                            $memo = $request->get($type . '_ng_comme' . $i);
//                            $this->Deny($type, $date, $deploy, $uid, $memo);
                            $deny_maile = true;
                            if( $name_list ) $name_list .= " / ";
                            $name_list .= $this->getName($uid);
                        } else {
                            if( $type == 'yo' ) {
                                if( $pos_no >= $root ) {
                                    // �ǽ���ǧ�ޤǾ�ǧ�������Ȥ�ǧ�ԡʲ�Ĺ����Ĺ�ˤؤ��Τ餻�������
//                                    $this->Notice($date, $deploy, $uid);
                                    $notice_maile = true;
                                    if( $name_list ) $name_list .= " / ";
                                    $name_list .= $this->getName($uid);
                                }
                            }
                        }
                        $up_flag = true;
/* �������Τ��Τ餻�ϡ��ǽ�ξ�ǧ�ԤΤߤǤ褤�� *
                        if( $type == 'ji' && $request->get('yo_root' . $i . '_' . $r) == '' ) {
                            $this->AfterReport($date, $deploy, $uid); // ����������
                        }
/**/
                    }
                }
//                if( $up_flag && $hurry_maile ) $this->Hurry($date, $deploy, "");
                if( $up_flag && $next_maile ) $this->NextMaile($date, $deploy);
                if( $notice_maile ) $this->Notice2($date, $deploy, $name_list);
                if( $deny_maile )   $this->Deny2($type, $date, $deploy, $name_list, $memo);

                // �����Ȥ���Ͽ
                $pos_na = $this->getPostsName();
                $name = 'comment_' . $pos_na . $i;
                if( $comment = $request->get($name) ) {
                    $set = "comment_$pos_na='$comment'";
                    $where = "date='$date' AND deploy='$deploy'";
                    $this->ReportUpDate($set, $where);
                }
            }
            if( $up_flag ) $this->Result2($date, $deploy); // �Ķȷ������������ϴ�λ�� Result2��
        }
        $_SESSION['s_sysmsg'] .= "������¹Ԥ��ޤ�����";
    }

    // ������Ժ߼Լ���
    public function getDeployAbsence(&$res, &$ka, &$bu)
    {
        $where  = $this->getWhereDeploy();              // (deploy='xxx' OR deploy='xxx')
        $rows   = $this->GetDeployName($where, $res);   // ����̾�����
        $pos_no = $this->getPostsNo();  // 1 or 2 or 3
        $now    = date('Ymd');  // ����ǯ����
        for( $n=0; $n<$rows; $n++ ) {    // �Ժ߼ԥ����å�
            switch ($pos_no) {
                case 3:   // ����Ĺ�ʤ���Ĺ�ʲ�Ĺ��ޤ�ˤνжг�ǧ
                    $res[$n][2] = $this->IsAbsence($now, $this->getButyouUID($res[$n][0]));
//$res[$n][2] = true;   // TEST
                    if( $res[$n][2] ) {
                        $bu = true; // ��Ĺ�Ժ�
                        $res[$n][1] = $this->IsAbsence($now, $this->getKatyouUID($res[$n][0]));
//$res[$n][1] = true;   // TEST
//$res[$n][1] = false;   // TEST
                        if( $res[$n][1] ) $ka = true;    // ��Ĺ�Ժ�
                    }
                    break;
                case 2:   // ��Ĺ�ʤ��Ĺ�νжг�ǧ
                    $res[$n][1] = $this->IsAbsence($now, $this->getKatyouUID($res[$n][0]));
//$res[$n][1] = true;   // TEST
                    if( $res[$n][1] ) $ka = true;    // ��Ĺ�Ժ�
                    break;
            }
        }
        return $rows;
    }

    // �Ժ�̤��ǧ�ǡ���
    public function GetUnapproved($d_res, $d_rows, &$where, &$res)
    {
        $column = "yo_ad_";
        $pos_no = $this->getPostsNo();  // 1 or 2 or 3
        $where1 = "";
        for( $n=0; $n<$d_rows; $n++ ) {
            if( $pos_no==3 && $d_res[$n][2] ) {  // ����Ĺ�ΤȤ��������������Ĺ�Ժ�
                if( $where1 ) $where1 .= " OR ";
                $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "bu='m' AND (yo_ad_ka!='m' OR yo_ad_ka IS NULL) )";
                if( $d_res[$n][1] ) {  // ��������β�Ĺ�Ժ�
                    if( $where1 ) $where1 .= " OR ";
                    $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "ka='m')";
                }
            } else if( $pos_no==2 && $d_res[$n][1] ) {   // ��Ĺ�ΤȤ�����������β�Ĺ�Ժ�
                if( $where1 ) $where1 .= " OR ";
                $where1 .= "(deploy='{$d_res[$n][0]}' AND " . $column . "ka='m')";
            }
        }
        if( ! $where1 ) return -1;
        
        $where2 = $column . "st<=" . ($pos_no-2);    // xx_ad_st=(x-2)
        $where .= " AND (" . $where1 . ") AND " . $where2;   // xx_ad_xx='m' AND (deploy='xxx��' OR deploy='xxx��') AND xx_ad_st=(x-1)
        $rows = $this->GetDateDeploy($where, $res); // ̤��ǧ�Τ������դ���������
        return $rows;
    }

    // �������������Ĺ��ޤ�actID����
    public function getBuKatyouActID($b_name)
    {
        $where = "";
        
        if( $b_name == "��̳��" ) {
            $where = "(ct.act_id=605 OR ct.act_id=610 OR ct.act_id=650 OR ct.act_id=651 OR ct.act_id=660) ";
        } else if( $b_name == "���ʴ�����" ) {
            $where = "(ct.act_id=610 OR ct.act_id=670) ";
        } else if( $b_name == "�ʼ��ݾڲ�" ) {
            $where = "(ct.act_id=501 OR ct.act_id=174 OR ct.act_id=517 OR ct.act_id=537 OR ct.act_id=581) ";
        } else if( $b_name == "���Ѳ�" ) {
            $where = "(ct.act_id=501 OR ct.act_id=173 OR ct.act_id=515 OR ct.act_id=535) ";
        } else if( $b_name == "��¤�� ��¤����" ) { // 600 ��ޤ�Ǥ���١����ˡ�"AND ud.uid!=999999 AND ud.pid!=110" �ɲ�
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=518 OR ct.act_id=519 OR ct.act_id=556 OR ct.act_id=520) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $b_name == "��¤�� ��¤����" ) { // 600 ��ޤ�Ǥ���١����ˡ�"AND ud.uid!=999999 AND ud.pid!=110" �ɲ�
            $where = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=547 OR ct.act_id=527 OR ct.act_id=528) AND ud.uid!=999999 AND ud.pid!=110";
        } else if( $b_name == "���������� �ײ衦���㷸" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=512 OR ct.act_id=532 OR ct.act_id=513 OR ct.act_id=533) ";
        } else if( $b_name == "���������� ��෸" ) {
            $where = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=514 OR ct.act_id=534) ";
        } else if( $b_name == "���ץ���Ω�� ɸ�෸�ͣ�" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=522) ";
        } else if( $b_name == "���ץ���Ω�� ɸ�෸�ȣ�" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=523) ";
        } else if( $b_name == "���ץ���Ω�� ����" ) {
            $where = "(ct.act_id=500 OR ct.act_id=176 OR ct.act_id=525) ";
        } else if( $b_name == "��˥���Ω��" ) {
            $where = "(ct.act_id=500 OR ct.act_id=551 OR ct.act_id=175 OR ct.act_id=572) ";
        }
        
        return $where;
    }
    
    // ����UID���ԺߤǤ�����
    public function IsAbsence($date, $uid)
    {
        // 8:30�������0��꾮�������ˤϡ�����Ū�˵��Ȥߤʤ���
        if( strtotime(date("H:i:s")) - strtotime('8:30:00') < 0 ) return false;

        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) {
            return false;
        }
        return true;
    }

    // ��������β�Ĺ����Ĺ���� UID
    public function getKatyouUID($b_name)
    {
        $where_act = $this->getBuKatyouActID($b_name);
        
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           $where_act AND (ud.pid=46 OR ud.pid=50 )
                 ";
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) <= 0 ) return false;
        
        return $res[0][0];
    }

    // �����������Ĺ����Ĺ������95=������Ĺ��UID
    public function getButyouUID($b_name)
    {
        $where_act = $this->getBuKatyouActID($b_name);
        
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           $where_act AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95 )
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return $res[0][0];
    }

    // ����Ĺ��UID
    public function getKoujyotyouUID()
    {
        $query = "
                    SELECT          ud.uid
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           ct.act_id=600 AND ud.sid=99 AND ud.pid=110
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) return false;
        
        return $res[0][0];
    }

// ============================================================================
// �Ȳ� =======================================================================
// ============================================================================
    // �Ȳ��̤�ɽ������ǡ��������
    public function getResultsView($request, &$res)
    {
        $d_radio = $request->get("days_radio");
        $date    = $request->get("ddlist_year") . $request->get("ddlist_month"). $request->get("ddlist_day");
        $date2   = $request->get("ddlist_year2") . $request->get("ddlist_month2"). $request->get("ddlist_day2");
        $deploy  = $request->get("ddlist_bumon");
        $s_no    = $request->get("s_no");
        $m_radio = $request->get("mode_radio");
//        $_SESSION['s_sysmsg'] .= "getResultsView() select=$select, date=$date, date2=$date2, deploy=$deploy, s_no=$s_no";
        
        if( $d_radio == 1 ) {
            $where = "date='$date'";
        } else if( $d_radio == 2 ) {
            $where = "date>='$date' AND date<='$date2' ";
        } else {
            return -1;
        }
        if( $deploy != '---- ���򤷤Ʋ����� ----' ) {
            $where .= " AND deploy='$deploy' ";
        } else {
            $where .= " AND {$this->getWhereDeploy()} ";
        }
        if( $s_no ) {
            $where .= " AND uid='$s_no' ";
        }
        if( $m_radio == 2 ) {
            $where .= " AND yo_ad_rt!='-1' AND (ji_ad_rt='0' OR ji_ad_rt IS NULL) ";
        } else if( $m_radio == 3 ) {
            $where .= " AND ji_ad_rt!='0' ";
        }
        $where .= " AND (yo_ad_st IS NOT NULL OR ji_ad_st IS NOT NULL) ";
        
        $query = "SELECT * FROM over_time_report WHERE $where ORDER BY date, deploy, no";
        
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
//            $_SESSION['s_sysmsg'] .= '������' . $query . '��';  // ���顼
        }
//        $_SESSION['s_sysmsg'] .= "����($rows) $query";  // ���顼

        return $rows;
    }

    // ����UID�����դνжл��ּ���
    public function getWorkingStrTime($uid, $date)
    {
        $date = substr($date, 0,4) . substr($date, 5,2) . substr($date, 8,2);
        $query = "SELECT str_time FROM working_hours_report_data_new WHERE uid='$uid' AND working_date='$date'";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return "----";
        }
        return $res[0][0];
    }

    // ����UID�����դ���л��ּ���
    public function getWorkingEndTime($uid, $date)
    {
        $date = substr($date, 0,4) . substr($date, 5,2) . substr($date, 8,2);
        $query = "SELECT end_time FROM working_hours_report_data_new WHERE uid='$uid' AND working_date='$date'";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0 ) {
            return "----";
        }
//        $res[0][0] = substr_replace($res[0][0], ":", 2, 0);
//        if($res[0][0] == "00:00") $res[0][0] = "<font style='background-color:yellow; color:blue;'>" . $res[0][0] . "</font>";
        return $res[0][0];
    }

// ============================================================================
// �ƥ��� =====================================================================
// ============================================================================
    // TEST �����᡼��
    public function TEST()
    {
        // ����Ĺ����Ĺ����Ĺ
        $where = "(ud.pid=110)";
        $where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
        $where = "(ud.pid=46 OR ud.pid=50)";
        $where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";
        
        // ����Ĺ�� uid �� act_id ����
        $query = "
                    SELECT          uid, ct.act_id, ud.pid, trim(name)
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           retire_date IS NULL AND $where
                 ";
        $res_list = array();
        if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // �����ԲĤʤ齪λ��
        
        for( $r=0; $r<$rows_list; $r++ ) {
            $bu_act = 0;    // �����
            // ������
            $where = "WHERE yo_ad_rt!='-1' AND ";
            if( $res_list[$r][1] == 600 ) {  // ����Ĺ
                if( $res_list[$r][2] == 95 ) {  // ������Ĺ
                    $res_list[$r][1] = 582; // ��¤����act_id���åȡ����Ƚ�Ǥ���ݤ˻��ѡ�
                    $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
                } else {
                    $where .= "yo_ad_st=2 AND yo_ad_ko='m' AND (deploy IS NOT NULL)";
                }
            } else if( $res_list[$r][1] == 610 ) {   // ������
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��̳��' OR deploy='���ʴ�����')";
            } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // �ɣӣϻ�̳�� ������ ��̳�� ��̳ ��̳
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��̳��')";
                $bu_act = 610;
            } else if( $res_list[$r][1] == 670 ) {   // ������ ���ʴ�����
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���ʴ�����')";
                $bu_act = 610;
            } else if( $res_list[$r][1] == 501 ) {   // ������
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='�ʼ��ݾڲ�' OR deploy='���Ѳ�')";
            } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // ������ �ʼ�������
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='�ʼ��ݾڲ�')";
                $bu_act = 501;
            } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // ������ ���Ѳ�
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���Ѳ�')";
                $bu_act = 501;
            } else if( $res_list[$r][1] == 582 ) { // ��¤��
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
            } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // ��¤�� ��¤����
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��¤�� ��¤����')";
                $bu_act = 582;
            } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // ��¤�� ��¤����
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��¤�� ��¤����')";
                $bu_act = 582;
            } else if( $res_list[$r][1] == 500 ) { // ������
                $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸' OR deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����' OR deploy='��˥���Ω��')";
            } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // ������ ����������
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸')";
                $bu_act = 500;
            } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // ������ ���ץ���Ω��
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����')";
                $bu_act = 500;
            } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // ������ ��˥���Ω��
                $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='��˥���Ω��')";
                $bu_act = 500;
            } else {
                $where .= "(deploy IS NULL)";   // ���顼
            }
            // ��ǧ�Ԥ��������
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where";
            $res_count = array();
            $rows_ken  = getResult($query, $res_count);
            
            if( $rows_ken <= 0 ) continue; // ��ǧ�Ԥ�̵���ʤ鼡��
            

// �Ժߥ����å�����
$superiors = false;         // ��Ĺ���Υե饰�ʽ������
$date = date('Ymd');        // ���������ռ���
$uid = $res_list[$r][0];    // ���Ȥ�UID
$query = "
            SELECT uid FROM working_hours_report_data_new
            WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
         ";
$res = array();
if( getResult2($query, $res) > 0 && $res_list[$r][2] != 110 ) {
    $kojyo = false;     // ����Ĺ���Υե饰�ʽ������
    if( $res_list[$r][2]==46 || $res_list[$r][2]==50 ) {
        // ��Ĺ�ˤʤ�Τǡ���Ĺ�γ�ǧ���Ժߤʤ鹩��Ĺ�ޤ�
        for( $n=0; $n<$rows_list; $n++ ) {
            if( $res_list[$n][1] == $bu_act ) {
                $uid = $res_list[$n][0];
                break; // ���Ȥ���Ĺ �ޤ�
            }
        }
        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( getResult2($query, $res) <= 0 ) {
            $superiors = true;  // ��Ĺ���Υե饰��ON��
        } else {
            $kojyo = true;  // ����Ĺ���Υե饰��ON��
        }
    } else {
        $kojyo = true;  // ����Ĺ���Υե饰��ON��
    }
    // ����Ĺ�����å�
    if( $kojyo ) {
        for( $n=0; $n<$rows_list; $n++ ) {
            if( $res_list[$n][1] == 600 ) {
                $uid = $res_list[$n][0];
                break; // ����Ĺ �ޤ�
            }
        }
        $query = "
                    SELECT uid FROM working_hours_report_data_new
                    WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( getResult2($query, $res) <= 0 ) {
            $superiors = true;  // ��Ĺ���Υե饰��ON��
        }
    }
}

            // �᡼�����ɥ쥹����
            $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
            $where = "WHERE uid='{$uid}'";  // uid
            $where = "WHERE uid='300667'";  // uid �����ѹ� ����꡼�����ϡ������Ȳ�
            $query .= $where;   // SQL query ʸ�δ���
            $res_mail = array();
            if( getResult($query, $res_mail) <= 0 ) continue; // �᡼�륢�ɥ쥹�����ԲĤʤ鼡��
            
            // �᡼�����������
            $sendna = $res_mail[0][0];  // ̾��
            $sendna = $res_list[$r][3]; // ̾�� �����ѹ� ����꡼�����ϡ������Ȳ�
            $mailad = $res_mail[0][1];  // �᡼�륢�ɥ쥹
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
if( $superiors ) $sendna = $res_list[$n][3];  // ̾�� �����ѹ� ����꡼�����ϡ������Ȳ�
if( $superiors ) {
            $attenSubject = "{$sendna} �� ���Ժ�̤��ǧ�� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
} else {
            $attenSubject = "{$sendna} �� ��̤��ǧ�� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
}
            $message  = "{$sendna} ��\n\n";
if( $superiors ) {
            $message .= "{$res_list[$r][3]} �� �Ժߤΰ١������\n\n";
            $message .= "����ֳ���ȿ���ʻ��������˾�ǧ�����򤪴ꤤ���ޤ���\n\n";
            $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$uid}&showMenu=Judge&select_radio=2\n\n";
} else {
            if( $rows_ken <= 0 ) {
                $message .= "����ֳ���ȿ���ʻ��������˾�ǧ�Ԥ��Ϥ���ޤ���\n\n";
            } else {
                $message .= "����ֳ���ȿ���ʻ��������˾�ǧ�Ԥ��� {$rows_ken} �濫��ޤ���\n\n";
                $message .= "��ǧ�����򤪴ꤤ���ޤ���\n\n";
                // ��ǧ�ڡ����Υ��ɥ쥹(Uid)��ɽ��������å��Ǿ�ǧ�ڡ�����
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge\n\n";
            }
}
            $message .= "�ʾ塣";
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
    
    // TEST ������᡼��
    public function TEST2()
    {
        // ����Ĺ����Ĺ����Ĺ
        $where = "(ud.pid=110)";
        $where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
        $where = "(ud.pid=46 OR ud.pid=50)";
        $where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";
        
        // ����Ĺ�� uid �� act_id ����
        $query = "
                    SELECT          uid, ct.act_id, pid, trim(name)
                    FROM            user_detailes   AS ud
                    LEFT OUTER JOIN cd_table        AS ct   USING(uid)
                    WHERE           retire_date IS NULL AND $where
                 ";
        $res_list = array();
        if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // �����ԲĤʤ齪λ��
        
        for( $r=0; $r<$rows_list; $r++ ) {
            // ������
            if( $res_list[$r][1] == 600 ) {  // ����Ĺ
                if( $res_list[$r][0] == '012394' ) {  // ������Ĺ
                    $deploy = "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
                } else {
                    $deploy = "(deploy IS NOT NULL)";
                }
            } else if( $res_list[$r][1] == 610 ) {   // ������
                $deploy = "(deploy='��̳��' OR deploy='���ʴ�����')";
            } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // �ɣӣϻ�̳�� ������ ��̳�� ��̳ ��̳
                $deploy = "(deploy='��̳��')";
            } else if( $res_list[$r][1] == 670 ) {   // ������ ���ʴ�����
                $deploy = "(deploy='���ʴ�����')";
            } else if( $res_list[$r][1] == 501 ) {   // ������
                $deploy = "(deploy='�ʼ��ݾڲ�' OR deploy='���Ѳ�')";
            } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // ������ �ʼ�������
                $deploy = "(deploy='�ʼ��ݾڲ�')";
            } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // ������ ���Ѳ�
                $deploy = "(deploy='���Ѳ�')";
            } else if( $res_list[$r][1] == 582 ) { // ��¤��
                $deploy = "(deploy='��¤�� ��¤����' OR deploy='��¤�� ��¤����')";
            } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // ��¤�� ��¤����
                $deploy = "(deploy='��¤�� ��¤����')";
            } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // ��¤�� ��¤����
                $deploy = "(deploy='��¤�� ��¤����')";
            } else if( $res_list[$r][1] == 500 ) { // ������
                $deploy = "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸' OR deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����' OR deploy='��˥���Ω��')";
            } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // ������ ����������
                $deploy = "(deploy='���������� �ײ衦���㷸' OR deploy='���������� ��෸')";
            } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // ������ ���ץ���Ω��
                $deploy = "(deploy='���ץ���Ω�� ɸ�෸�ͣ�' OR deploy='���ץ���Ω�� ɸ�෸�ȣ�' OR deploy='���ץ���Ω�� ����')";
            } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // ������ ��˥���Ω��
                $deploy = "(deploy='��˥���Ω��')";
            } else {
                $deploy = "(deploy IS NULL)";   // ���顼
            }
            // ����������
            $noinput1 = "yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt=0 AND date!=date('today')";
            if( $res_list[$r][2] == 110 ) {
                $noinput = "yo_ad_ka IS NULL AND yo_ad_bu IS NULL";
                $noadmit = "ji_ad_ko='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m') AND (ji_ad_bu IS NULL OR ji_ad_bu!='m')";
            } else if( $res_list[$r][2] == 47 || $res_list[$r][2] == 70 || $res_list[$r][2] == 95 ) {
                $noinput = "yo_ad_ka IS NULL";
                $noadmit = "ji_ad_bu='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m')";
            } else if( $res_list[$r][2] == 46 || $res_list[$r][2] == 50 ) {
                $noinput = "yo_ad_ka!=''";
                $noadmit = "ji_ad_ka='m'";
            } else {
                $noinput = $noadmit = $deploy;
            }
            $where_noinput = "WHERE {$noinput1} AND {$noinput} AND {$deploy}";
            $where_noadmit = "WHERE {$noadmit} AND {$deploy}";
            
            // ������̤���ϼ���
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noinput";
            $res_noinput  = array();
            $rows_noinput = getResult($query, $res_noinput);
/**            
            // ������̤��ǧ����
            $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noadmit";
            $res_noadmit  = array();
            $rows_noadmit = getResult($query, $res_noadmit);
/**/            
            // �᡼�����ɥ쥹����
            $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
            $where = "WHERE uid='{$res_list[$r][0]}'";   // uid
            $where = "WHERE uid='300667'";   // TEST ����Ū��
            $query .= $where;   // SQL query ʸ�δ���
            $res_mail = array();
            if( getResult($query, $res_mail) <= 0 ) continue; // �᡼�륢�ɥ쥹�����ԲĤʤ鼡��
            
            // �᡼�����������
            $sendna = $res_mail[0][0];  // ̾��
            $sendna = $res_list[$r][3]; // TEST ����Ū�� ̾��
            $mailad = $res_mail[0][1];  // �᡼�륢�ɥ쥹
            $_SESSION['u_mailad']  = $mailad;
            $to_addres = $mailad;
            $add_head = "";
//            $attenSubject = "{$sendna} �� �ڻĶȷ���������� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
            $attenSubject = "{$sendna} �� ��̤���ϡ� ����ֳ���ȿ����ꤪ�Τ餻"; // ���衧 
            $message = "{$sendna} ��\n\n";
            $message .= "����ֳ���ȿ���ʻĶȷ������";
            
            if( $rows_noinput <= 0 ) continue; // ̤����̵���ʤ鼡��
            
            if( $rows_noinput <= 0 ) {
//                $message .= "����ֳ���ȿ���ʻĶȷ������̤���ϤϤ���ޤ���\n\n";
                $message .= "��̤ �� �ϡ�����ޤ���\n\n";
            } else {
//                $message .= "����ֳ���ȿ���ʻĶȷ������̤���Ϥ� {$rows_noinput} �濫��ޤ���\n\n";
                $message .= "̤���Ϥ� {$rows_noinput} �濫��ޤ���\n";
                $message .= "------------------------------------------------------------------\n";
                for( $n=0; $n<$rows_noinput; $n++ ) {
                    $week   = array(' (��)',' (��)',' (��)',' (��)',' (��)',' (��)',' (��)');
                    $date   = $res_noinput[$n][0];
                    $day_no = date('w', strtotime($date));
                    $date   = $res_noinput[$n][0] . $week[$day_no];
                    $message .= "���������{$date}\t����̾��{$res_noinput[$n][1]}\n";
                }
//                $message .= "\n����ֳ���ȿ���ʻĶȷ�����ˤ����Ϥ���褦Ϣ���Ʋ�������\n\n";
                $message .= "------------------------------------------------------------------\n";
                $message .= "���Ϥ���褦Ϣ���Ʋ�������\n\n";
            }
/**
            if( $rows_noadmit <= 0 ) {
//                $message .= "����ֳ���ȿ���ʻĶȷ�����˾�ǧ�Ԥ��Ϥ���ޤ���\n\n";
                $message .= "����ǧ�Ԥ�������ޤ���\n\n";
            } else {
//                $message .= "����ֳ���ȿ���ʻĶȷ�����˾�ǧ�Ԥ��� {$rows_noadmit} �濫��ޤ���\n\n";
                $message .= "����ǧ�Ԥ���{$rows_noadmit} �濫��ޤ�����ǧ�����򤪴ꤤ���ޤ���\n\n";
//                $message .= "����ֳ���ȿ���ʻĶȷ�����˾�ǧ�����򤪴ꤤ���ޤ���\n\n";
                // ��ǧ�ڡ����Υ��ɥ쥹(Uid)��ɽ��������å��Ǿ�ǧ�ڡ�����
//                $message .= "���ϺѤߤο��𤬤ʤ����ϰʲ��� URL ����ǧ���Ʋ�������\n\n";
//                $message .= "������ ����ֳ���ȿ���ʻĶȷ������̤��ǧ�ڡ��� ������\n\n";
                $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge&select_radio=3\n\n";
            }
/**/
            $message .= "�ʾ塣";
/**/
            if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                // ���ʼԤؤΥ᡼�������������¸
                //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
            }
            ///// Debug
            //if ($cancel) {
            //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
            //}
/**/
        }
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
} // Class over_time_work_report_Model End

?>
