<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ �ǹ礻(���)�������塼��ɽ�ξȲ񡦥��ƥʥ�                  //
//                                                            MVC Model ��  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Model.php                          //
// 2005/11/21 ���ʼԤΥ��롼�׻�����ɲ�                                    //
// 2005/11/22 getViewList()�᥽�åɤ�subject�β��Ԥ�<br>���ִ����뵡ǽ����  //
//            get_caption()�᥽�åɤ�List���ʳ������դ�Ф��ʤ�(�����ѹ�)   //
//            guideMeetingMail()��$request->get('year')��get('yearReg')���� //
// 2005/11/27 ���롼���Խ��᥽�å�group_edit()�˻��礬Ʊ���������å����ɲ�  //
// 2005/12/05 duplicateCheck()�᥽�åɤη�̾�˲��Ԥ���������б��ɲ�      //
// 2005/12/27 �᡼���ʸ�������к��Τ��� subject��Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�  //
// 2006/05/09 ��ʬ�Υ������塼��Τ�ɽ��(�ޥ��ꥹ��)��ǽ���ɲ�              //
// 2006/06/19 duplicateCheck()�᥽�åɤ�$this->where����(���դ��Ѥ����к�)//
// 2006/07/24 guideMeetingMail()�ѹ�   �᡼���������դα���������ɽ���ɲ� //
// 2007/03/06 ���롼���Խ����ι�ץ쥳���ɿ�����SQLʸ���Զ�����         //
//            mb_send_mail()message�β��ԥ����ɤ�\r\n��\n�إޥ˥奢��˽��� //
// 2007/04/05 debug�ѤΥ᡼������ tnksys@ �򥳥��ȥ�����                  //
// 2007/05/08 guideMeetingMail()�᥽�åɤ�$subject2/$subject3���ɲ�         //
//            str_replace("\n", '...') �� str_replace("\r\n", '...')���ѹ�  //
// 2007/05/10 ��ĺ�����˥���󥻥�Υ᡼�������Τ���delete()�᥽�åɤ��ѹ�//
//            �ڤ�guideMeetingMail()�᥽�åɤ򥭥�󥻥��б����ѹ�          //
// 2009/12/17 �Ȳ񡦰��������ɲäΥƥ��ȡ�Print��                      ��ë //
// 2015/06/19 �ײ�ͭ��ξȲ���ɲ�                                     ��ë //
// 2017/11/06 ��ļ��μ���execute_ListNotPageControl���ѹ�             ��ë //
// 2019/03/15 �䲹�嵡��Ư���������Ѽ֡��Ժ߼ԤΥ�˥塼���ɲ�         ��ë //
// 2019/03/19 cardupCheck()�ѹ��κݤ�$serial_no��ȴ���Ƥ����Τǽ���    ��ë //
// 2021/06/10 ����������ư�Ѥ�ǯ��Ϥ���ʤ��ä��ΤǺ��             ��ë //
// 2021/07/14 �Ұ����������칩��Ȥ���¾�����ɽ�����ʤ��褦�ѹ�       ��ë //
// 2021/11/17 ����Ͼ�ǧ�Ԥ������ɽ����Ϣ���ɲ�                       ���� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../ComTableMntClass.php');   // TNK ������ �ơ��֥����&�ڡ�������Class


/******************************************************************************
*     �ǹ礻(���)�������塼���� MVC��Model�� base class ���쥯�饹�����     *
******************************************************************************/
class MeetingSchedule_Model extends ComTableMnt
{
    ///// Private properties
    private $where;
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        $listSpan   = $request->get('listSpan');
        $room_no    = $request->get('room_no');
        $car_no     = $request->get('car_no');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        $OnOff      = $request->get('OnOff');
        if ($str_date == '') {
            $str_date = $year . $month . $day;
        }
        if ($end_date == '') {
            $end_date = $year . $month . $day;
        }
        switch ($request->get('showMenu')) {
        case 'Room':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_room_master {$this->where}
            ";
            break;
       case 'Car':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_car_master {$this->where}
            ";
            break;
         case 'Group':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM (SELECT count(group_no) FROM meeting_mail_group GROUP BY group_no {$this->where})
                AS meeting_group
            ";
            break;
        case 'MyList':
            $this->where = "'{$_SESSION['User_ID']}', timestamp '{$year}-{$month}-{$day} 00:00:00', timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day'";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_mylist({$this->where})
            ";
            break;
        case 'Print' :
            if ($room_no != '') {
                $this->where = "WHERE room_no = {$room_no} and to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            } else {
                $this->where = "WHERE to_char(str_time, 'YYYYMMDD') >= {$str_date} and to_char(end_time, 'YYYYMMDD') <= {$end_date}";
            }
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        case 'Holyday'  :
            $this->where = "WHERE acq_date>='{$year}-{$month}-{$day}' AND acq_date<=(timestamp '{$year}-{$month}-{$day}' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM user_holyday {$this->where}
            ";
            break;
        case 'Absence'  :
            $this->where = "WHERE (start_date = '{$year}-{$month}-{$day}' OR (start_date <= '{$year}-{$month}-{$day}' AND end_date >= '{$year}-{$month}-{$day}'))
                              AND admit_status != 'CANCEL' AND admit_status != 'DENY' 
                              AND content!='ID�������̤�˺��ʽжС� '
                              AND content!='ID�������̤�˺�����С� '
                              AND content!='���¾�ǧ˺��ʻĶȿ���ϳ���'
                              AND content!='ID�������̤�˺�����Сˡ� ���¾�ǧ˺��ʻĶȿ���ϳ���'
                            ";
            $sql_sum = "
                SELECT count(*) FROM sougou_deteils {$this->where}
            ";
            break;
        case 'List'  :
        case 'Apend' :
        case 'Edit'  :
        default      :
            $this->where = "WHERE str_time>='{$year}-{$month}-{$day} 00:00:00' AND str_time<=(timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        }
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($sql_sum, $request, 'meeting_schedule.log');
    }
    
    ////////// ��ĥ������塼����ɲ�
    public function add($request)
    {
        ///// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ���ͽ���ǯ����
        $month      = $request->get('monthReg');            // ���ͽ��η��
        $day        = $request->get('dayReg');              // ���ͽ���������
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // ��ķ�̾ 2005/12/27 �����Ѵ��ɲ�
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $sponsor    = $request->get('sponsor');             // ��ż�
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����)
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $car_no     = $request->get('car_no');              // ���Ѽ��ֹ�
        $mail       = $request->get('mail');                // �᡼������� Y/N
        // ǯ�����Υ����å�  ���ߤ� Main Controller�ǽ���ͤ����ꤷ�Ƥ���Τ�ɬ�פʤ��������Τޤ޻Ĥ���
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        // ���ϡ���λ ���֤ν�ʣ�����å�
        if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no)) {
            if ($this->cardupCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $car_no)) {
                $count_a   = 0;                                 // ���ʼԿͿ��Υ������
                $count_a   = count($atten);
                $serial_no = $this->add_execute($request);
                if ($serial_no) {
                    if ($mail == 't') {
                        if ($this->guideMeetingMail($request, $serial_no)) {
                            $_SESSION['s_sysmsg'] = '�᡼����������ޤ�����';
                        } else {
                            $_SESSION['s_sysmsg'] = '�᡼�������Ǥ��ޤ���Ǥ�����';
                        }
                    }
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = '��Ͽ�Ǥ��ޤ���Ǥ�����';
                }
            }
        }
        return false;
    }
    
    ////////// ��ĥ������塼��δ������
    public function delete($request)
    {
        ///// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');           // ���ꥢ���ֹ�
        $subject    = $request->get('subject');             // ��ķ�̾
        $mail       = $request->get('mail');                // �᡼������� Y/N
        // �оݥ������塼���¸�ߥ����å�
        $chk_sql = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // ����Υ��ꥢ���ֹ��¸�ߥ����å�
            $_SESSION['s_sysmsg'] = "��{$subject}�פ�¾�οͤ��ѹ�����ޤ�����";
        } else {
            if ($mail == 't') {
                if ($this->guideMeetingMail($request, $serial_no, true)) {
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
    
    ////////// ��ĥ������塼����ѹ�
    public function edit($request)
    {
        ///// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');           // Ϣ��(�����ե������)
        $year       = $request->get('yearReg');             // ���ͽ���ǯ����
        $month      = $request->get('monthReg');            // ���ͽ��η��
        $day        = $request->get('dayReg');              // ���ͽ���������
        $subject    = mb_convert_kana($request->get('subject'), 'KV'); // ��ķ�̾ 2005/12/27 �����Ѵ��ɲ�
        $request->add('subject', $subject);
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $car_no     = $request->get('car_no');             // ���Ѽ��ֹ�
        $mail       = $request->get('mail');                // �᡼������� Y/N
        $reSend     = $request->get('reSend');              // �ѹ����Υ᡼��κ�����Yes/No
        // ǯ�����Υ����å�
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        
        $query = "
            SELECT subject FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        if ($this->getUniResult($query, $check) > 0) {  // �ѹ����Υ��ꥢ���ֹ椬��Ͽ����Ƥ��뤫��
            // ���ϡ���λ ���֤ν�ʣ�����å�
            if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no, $serial_no)) {
                if ($this->cardupCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $car_no, $serial_no)) {
                    $response = $this->edit_execute($request);
                    if ($response) {
                        if ($reSend == 't' && $mail == 't') {
                            if ($this->guideMeetingMail($request, $serial_no)) {
                                $_SESSION['s_sysmsg'] = '�᡼�����������ޤ�����';
                            } else {
                                $_SESSION['s_sysmsg'] = '�᡼��κ��������Ǥ��ޤ���Ǥ�����';
                            }
                        }
                        return true;
                    } else {
                        $_SESSION['s_sysmsg'] = '�ѹ��Ǥ��ޤ���Ǥ�����';
                    }
                }
            }
        } else {
            $_SESSION['s_sysmsg'] = "��{$subject}�פ�¾�οͤ��ѹ�����ޤ�����";
        }
        return false;
    }
    
    ////////// ��ļ�����Ͽ���ѹ�
    public function room_edit($room_no, $room_name, $duplicate)
    {
        ///// room_no��Ŭ�������å�
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name, duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // ��ļ�����Ͽ
            $response = $this->roomInsert($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} ����Ͽ���ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��ļ�����Ͽ������ޤ���Ǥ�����';
            }
        } else {
            // ��ļ����ѹ�
            // �ǡ������ѹ�����Ƥ��뤫�����å�
            if ($room_no == $res[0][0] && $room_name == $res[0][1] && $duplicate == $res[0][2]) return true;
            // ��ļ����ѹ� �¹�
            $response = $this->roomUpdate($room_no, $room_name, $duplicate);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} ���ѹ����ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '��ļ����ѹ�������ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ��ļ��� ���
    public function room_omit($room_no, $room_name)
    {
        ///// room_no��Ŭ�������å�
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT room_no, room_name FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} �Ϻ���оݥǡ���������ޤ���";
        } else {
            ///// ������Ƥ�����ʤ������Υǡ���������å�
            $query = "
                SELECT subject, to_char(str_time, 'YYYY/MM/DD') FROM meeting_schedule_header WHERE room_no={$room_no} limit 1;
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->roomDelete($room_no);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} �������ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} ��������ޤ���Ǥ�����";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} �ϲ�� [ {$res[0][1]} ] ������ [ {$res[0][0]} ] �ǻ��Ѥ���Ƥ��ޤ�������Ǥ��ޤ��� ̵���ˤ��Ʋ�������";
            }
        }
        return false;
    }
    
    ////////// ��ļ��� ͭ����̵��
    public function room_activeSwitch($room_no, $room_name)
    {
        ///// room_no��Ŭ�������å�
        if (!$this->checkRoomNo($room_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$room_no}] {$room_name} ���оݥǡ���������ޤ���";
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
                SELECT active FROM meeting_room_master WHERE room_no={$room_no}
            ";
            $update_sql = "
                UPDATE meeting_room_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE room_no={$room_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// ���Ѽ֤���Ͽ���ѹ�
    public function car_edit($car_no, $car_name, $car_dup)
    {
        ///// car_no��Ŭ�������å�
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT car_no, car_name, duplicate FROM meeting_car_master WHERE car_no={$car_no}
        ";
        $res = array();
        if ($this->getResult2($query, $res) <= 0) {
            // ���Ѽ֤���Ͽ
            $response = $this->carInsert($car_no, $car_name, $car_dup);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} ����Ͽ���ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '���Ѽ֤���Ͽ������ޤ���Ǥ�����';
            }
        } else {
            // ���Ѽ֤��ѹ�
            // �ǡ������ѹ�����Ƥ��뤫�����å�
            if ($car_no == $res[0][0] && $car_name == $res[0][1] && $car_dup == $res[0][2]) return true;
            // ���Ѽ֤��ѹ� �¹�
            $response = $this->carUpdate($car_no, $car_name, $car_dup);
            if ($response) {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} ���ѹ����ޤ�����";
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '���Ѽ֤��ѹ�������ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ���Ѽ֤� ���
    public function car_omit($car_no, $car_name)
    {
        ///// car_no��Ŭ�������å�
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT car_no, car_name FROM meeting_car_master WHERE car_no={$car_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} �Ϻ���оݥǡ���������ޤ���";
        } else {
            ///// ������Ƥ�����ʤ������Υǡ���������å�
            $query = "
                SELECT subject, to_char(str_time, 'YYYY/MM/DD') FROM meeting_schedule_header WHERE car_no={$car_no} limit 1;
            ";
            $res = array();
            if ($this->getResult2($query, $res) <= 0) {
                $response = $this->carDelete($car_no);
                if ($response) {
                    $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} �������ޤ�����";
                    return true;
                } else {
                    $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} ��������ޤ���Ǥ�����";
                }
            } else {
                $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} �ϲ�� [ {$res[0][1]} ] ������ [ {$res[0][0]} ] �ǻ��Ѥ���Ƥ��ޤ�������Ǥ��ޤ��� ̵���ˤ��Ʋ�������";
            }
        }
        return false;
    }
    
    ////////// ���Ѽ֤� ͭ����̵��
    public function car_activeSwitch($car_no, $car_name)
    {
        ///// car_no��Ŭ�������å�
        if (!$this->checkCarNo($car_no)) {
            return false;
        }
        $query = "
            SELECT active FROM meeting_car_master WHERE car_no={$car_no}
        ";
        if ($this->getUniResult($query, $active) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$car_no}] {$car_name} ���оݥǡ���������ޤ���";
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
                SELECT active FROM meeting_car_master WHERE car_no={$car_no}
            ";
            $update_sql = "
                UPDATE meeting_car_master SET
                active={$active}, last_date='{$last_date}', last_host='{$last_host}'
                WHERE car_no={$car_no}
            "; 
            return $this->execute_Update($update_sql, $save_sql);
        }
        return false;
    }
    
    ////////// ���ʼԥ��롼�פ���Ͽ���ѹ�
    public function group_edit($group_no, $group_name, $atten, $owner)
    {
        ///// group_no��Ŭ�������å�
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
                $_SESSION['s_sysmsg'] = '���ʼԥ��롼�פ���Ͽ������ޤ���Ǥ�����';
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
                $_SESSION['s_sysmsg'] = '���ʼԥ��롼�פ��ѹ�������ޤ���Ǥ�����';
            }
        }
        return false;
    }
    
    ////////// ���ʼԥ��롼�פ� ���
    public function group_omit($group_no, $group_name)
    {
        ///// group_no��Ŭ�������å�
        if (!$this->checkGroupNo($group_no)) {
            return false;
        }
        $query = "
            SELECT group_no, group_name FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        if ($this->getResult2($query, $res) <= 0) {
            $_SESSION['s_sysmsg'] = "[{$group_no}] {$group_name} �Ϻ���оݥǡ���������ޤ���";
        } else {
            ///// ������Ƥ�����ʤ������Υǡ���������å��Ϻ����ɬ�פʤ�
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
    
    ////////// ���ʼԥ��롼�פ� ͭ����̵��
    public function group_activeSwitch($group_no, $group_name)
    {
        ///// group_no��Ŭ�������å�
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
    ///// List��
    public function getViewList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ����         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 15
                ,car_name                               -- 16
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                meeting_car_master USING(car_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subject�β��Ԥ�<br>���ִ���
        }
        $result->add_array($res);
        return $rows;
    }
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// Holyday��
    public function getViewHolyday(&$result)
    {
        $query = "
            SELECT acq_date                       AS ������     -- 01
                ,trim(s.section_name)             AS ��°       -- 02
                ,d.uid                            AS �Ұ��ֹ�   -- 03
                ,trim(d.name)                     AS ��̾       -- 04
            FROM
                user_holyday AS h
            LEFT OUTER JOIN
                user_detailes AS d ON (h.uid=d.uid)
            LEFT OUTER JOIN
                section_master AS s ON (d.sid=s.sid)
            {$this->where}
            ORDER BY
                acq_date ASC, s.section_name ASC, d.uid ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    ///// MyList��
    public function getViewMyList(&$result)
    {
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ����         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 15
            FROM
                meeting_schedule_mylist({$this->where}) AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            ORDER BY
                str_time ASC, end_time ASC
        ";
        /*
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ����         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 15
                ,room_name                              -- 16
            FROM
                meeting_schedule_mylist({$this->where}) AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                meeting_car_master USING(car_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            ORDER BY
                str_time ASC, end_time ASC
        ";
        */
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subject�β��Ԥ�<br>���ִ���
        }
        $result->add_array($res);
        return $rows;
    }
    ///// ���ʼԤ� List�� attendance ʣ���б�
    public function getViewAttenList(&$result, $serial_no)
    {
        $query_a = "
            SELECT serial_no                            -- 00
                ,atten                                  -- 01
                ,trim(name)                             -- 02
                ,CASE
                    WHEN mail THEN '������'
                    ELSE '̤����'
                 END                                    -- 03
            FROM
                meeting_schedule_attendance AS meet
            LEFT OUTER JOIN
                user_detailes ON (atten=uid)
            WHERE
                serial_no = {$serial_no}
            ORDER BY
                atten ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query_a, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// �Ȳ񡦰��� List��
    public function getPrintList(&$result)
    {
        $query_p = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YY/MM/DD HH24:MI')  -- 02
                ,to_char(end_time, 'YY/MM/DD HH24:MI')  -- 03
                ,room_name                              -- 04
                ,sponsor                                -- 05
                ,trim(name)             AS ��̾         -- 06
                ,atten_num                              -- 07
                ,CASE
                    WHEN end_time > CURRENT_TIMESTAMP
                    THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ����         -- 08
                ,to_char(meet.regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 09
                ,to_char(meet.last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 10
                ,meet.last_host                         -- 11
                ,to_char(str_time, 'YYYY')              -- 12
                ,to_char(str_time, 'MM')                -- 13
                ,to_char(str_time, 'DD')                -- 14
                ,to_char(end_time, 'YYYY')              -- 15
                ,to_char(end_time, 'MM')                -- 16
                ,to_char(end_time, 'DD')                -- 17
                ,CASE
                    WHEN mail THEN '��������' ELSE '�������ʤ�'
                 END                                    -- 18
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            LEFT OUTER JOIN
                user_detailes ON (sponsor=uid)
            {$this->where}
            ORDER BY
                room_no ASC, str_time ASC, end_time ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query_p, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        for ($i=0; $i<$rows; $i++) {
            $res[$i][1] = str_replace("\r\n", '<br>', $res[$i][1]);   // subject�β��Ԥ�<br>���ִ���
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ������μҰ��ֹ�Ȼ�̾�����
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
                sid != 31 AND sid != 95 AND sid != 90
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
    
    ///// Edit ���� 1�쥳����ʬ
    public function getViewEdit($serial_no, $result)
    {
        $query = "
            SELECT serial_no                    -- 00
                ,subject                        -- 01
                ,to_char(str_time, 'HH24:MI')   -- 02
                ,to_char(end_time, 'HH24:MI')   -- 03
                ,room_no                        -- 04
                ,sponsor                        -- 05
                ,atten_num                      -- 06
                ,mail                           -- 07
                ,room_name                      -- 08
                ,to_char(str_time, 'YYYY')      -- 09
                ,to_char(str_time, 'MM')        -- 10
                ,to_char(str_time, 'DD')        -- 11
                ,car_no                         -- 12
            FROM
                meeting_schedule_header
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
            WHERE
                serial_no = {$serial_no}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) >= 1) {
            $result->add_once('serial_no',  $res[0][0]);
            $result->add_once('subject',    $res[0][1]);
            $result->add_once('str_time',   $res[0][2]);
            $result->add_once('end_time',   $res[0][3]);
            $result->add_once('room_no',    $res[0][4]);
            $result->add_once('sponsor',    $res[0][5]);
            $result->add_once('atten_num',  $res[0][6]);
            $result->add_once('mail',       $res[0][7]);
            $result->add_once('room_name',  $res[0][8]);
            $result->add_once('editYear',   $res[0][9]);
            $result->add_once('editMonth',  $res[0][10]);
            $result->add_once('editDay',    $res[0][11]);
            $result->add_once('car_no',     $res[0][12]);
        }
        return $rows;
    }
    
    ///// List���� ɽ��(����ץ����)������
    public function get_caption($switch, $year, $month, $day)
    {
        switch ($switch) {
        case 'List':
            // $caption = '���(�ǹ礻) ����';
            $caption = '��';
            $caption = sprintf("%04dǯ%02d��%02d��{$caption}", $year, $month, $day);
            break;
        case 'Apend':
            $caption = '���(�ǹ礻)���ɲ�';
            break;
        case 'Edit':
            $caption = '���(�ǹ礻)���Խ�';
            break;
        default:
            $caption = '';
        }
        return $caption;
        
    }
    
    ///// List���� ��Ͽ�ǡ������ʤ����Υ�å���������
    public function get_noDataMessage($year, $month, $day)
    {
        if ($year != '') {
            if (sprintf('%04d%02d%02d', $year, $month, $day) < date('Ymd')) {
                $noDataMessage = '��Ͽ������ޤ���';  // ���ξ��
            } else {
                $noDataMessage = 'ͽ�꤬����ޤ���';  // ̤��ξ��
            }
        } else {
            // �����ξ��
            $noDataMessage = 'ͽ�꤬����ޤ���';
        }
        return $noDataMessage;
        
    }
    
    ///// ��ļ��� List��
    public function getViewRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
                ,CASE
                    WHEN duplicate THEN '����'
                    ELSE '���ʤ�'
                 END                    AS ��ʣ         -- 02
                ,CASE
                    WHEN active THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ͭ��̵��     -- 03
                ,to_char(regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 05
            FROM
                meeting_room_master
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ��ļ��� <select>ɽ���� List��
    public function getActiveRoomList(&$result)
    {
        $query = "
            SELECT room_no                              -- 00
                ,room_name                              -- 01
            FROM
                meeting_room_master
            WHERE
                active IS TRUE
            ORDER BY
                room_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ���Ѽ֤� List��
    public function getViewCarList(&$result)
    {
        $query = "
            SELECT car_no                              -- 00
                ,car_name                              -- 01
                ,CASE
                    WHEN duplicate THEN '����'
                    ELSE '���ʤ�'
                 END                    AS ��ʣ         -- 02
                ,CASE
                    WHEN active THEN 'ͭ��'
                    ELSE '̵��'
                 END                    AS ͭ��̵��     -- 03
                ,to_char(regdate AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 04
                ,to_char(last_date AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI')
                                                        -- 05
            FROM
                meeting_car_master
            ORDER BY
                car_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ���Ѽ֤� <select>ɽ���� List��
    public function getActiveCarList(&$result)
    {
        $query = "
            SELECT car_no                              -- 00
                ,car_name                              -- 01
            FROM
                meeting_car_master
            WHERE
                active IS TRUE
            ORDER BY
                car_no ASC
        ";
        $res = array();
        if ( ($rows=$this->execute_ListNotPageControl($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ���ʼԥ��롼�פ� List��
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
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ���ʼԥ��롼�פ� �����롼��ʬ Attendance List��
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
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res);
        return $rows;
    }
    
    ///// ���ʼԥ��롼�פ�ͭ���ʥꥹ�� Active List��
    // JSgroup_name=���롼��̾�Σ���������, JSgroup_member=���롼��̾���б��������ʼԤΣ���������, �����=ͭ�����
    // owner='000000'�϶�ͭ���롼��, ���꤬������ϸĿͤΥ��롼��
    public function getActiveGroupList(&$JSgroup_name, &$JSgroup_member, $uid)
    {
        // �����
        $JSgroup_name = array();
        $JSgroup_member = array();
        // ���롼��̾������μ���
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
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// ��ļ���room_no��Ŭ��������å�����å������ܷ��(true=OK,false=NG)���֤�
    protected function checkRoomNo($room_no)
    {
        ///// room_no��Ŭ�������å�
        if (is_numeric($room_no)) {
            if ($room_no >= 1 && $room_no <= 32000) {   // int2���б�
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "��ļ����ֹ� {$room_no} ���ϰϳ��Ǥ��� 1��32000�ޤǤǤ���";
            }
        } else {
            $_SESSION['s_sysmsg'] = "��ļ����ֹ� {$room_no} �Ͽ����ʳ����ޤޤ�Ƥ��ޤ���";
        }
        return false;
    }
    
    ////////// ���Ѽ֤�car_no��Ŭ��������å�����å������ܷ��(true=OK,false=NG)���֤�
    protected function checkCarNo($car_no)
    {
        ///// car_no��Ŭ�������å�
        if (is_numeric($car_no)) {
            if ($car_no >= 1 && $car_no <= 32000) {   // int2���б�
                return true;
            } else {
                $_SESSION['s_sysmsg'] = "���Ѽ֤��ֹ� {$car_no} ���ϰϳ��Ǥ��� 1��32000�ޤǤǤ���";
            }
        } else {
            $_SESSION['s_sysmsg'] = "���Ѽ֤��ֹ� {$car_no} �Ͽ����ʳ����ޤޤ�Ƥ��ޤ���";
        }
        return false;
    }
    
    ////////// ��ļ���room_no��Ŭ��������å�����å������ܷ��(true=OK,false=NG)���֤�
    protected function checkGroupNo($group_no)
    {
        ///// group_no��Ŭ�������å�
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
    
    ////////// ��ļ�����Ͽ (�¹���)
    protected function roomInsert($room_no, $room_name, $duplicate)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // $duplicate �� 't' ���� 'f' �ʤΤ� ���Τޤ޻Ȥ�
        $insert_sql = "
            INSERT INTO meeting_room_master
            (room_no, room_name, duplicate, active, last_date, last_host)
            VALUES
            ('$room_no', '$room_name', '$duplicate', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ��ļ����ѹ� (�¹���)
    protected function roomUpdate($room_no, $room_name, $duplicate)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // $duplicate �� 't' ���� 'f' �ʤΤ� ���Τޤ޻Ȥ�
        $update_sql = "
            UPDATE meeting_room_master SET
            room_no={$room_no}, room_name='{$room_name}', duplicate='{$duplicate}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE room_no={$room_no}
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// ��ļ��κ�� (�¹���)
    protected function roomDelete($room_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM meeting_room_master WHERE room_no={$room_no}
        ";
        // �����SQLʸ������
        $delete_sql = "
            DELETE FROM meeting_room_master WHERE room_no={$room_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ���Ѽ֤���Ͽ (�¹���)
    protected function carInsert($car_no, $car_name, $car_dup)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // $duplicate �� 't' ���� 'f' �ʤΤ� ���Τޤ޻Ȥ�
        $insert_sql = "
            INSERT INTO meeting_car_master
            (car_no, car_name, duplicate, active, last_date, last_host)
            VALUES
            ('$car_no', '$car_name', '$car_dup', TRUE, '$last_date', '$last_host')
        ";
        return $this->execute_Insert($insert_sql);
    }
    
    ////////// ���Ѽ֤��ѹ� (�¹���)
    protected function carUpdate($car_no, $car_name, $car_dup)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM meeting_car_master WHERE car_no={$car_no}
        ";
        // $duplicate �� 't' ���� 'f' �ʤΤ� ���Τޤ޻Ȥ�
        $update_sql = "
            UPDATE meeting_car_master SET
            car_no={$car_no}, car_name='{$car_name}', duplicate='{$car_dup}', last_date='{$last_date}', last_host='{$last_host}'
            WHERE car_no={$car_no}
        "; 
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// ���Ѽ֤κ�� (�¹���)
    protected function carDelete($car_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM meeting_car_master WHERE car_no={$car_no}
        ";
        // �����SQLʸ������
        $delete_sql = "
            DELETE FROM meeting_car_master WHERE car_no={$car_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ���(�ǹ礻)�ΰ���� email �ǽФ���
    protected function guideMeetingMail($request, $serial_no, $cancel=false)
    {
        ///// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ���ͽ���ǯ����
        $month      = $request->get('monthReg');            // ���ͽ��η��
        $day        = $request->get('dayReg');              // ���ͽ���������
        $subject    = $request->get('subject');             // ��ķ�̾
        $subject2   = str_replace("\r\n", "\r\n������������", $subject);  // subject�β��Ԥ򥹥ڡ������ղä�����Τ��ִ���
        $subject3   = str_replace("\r\n", '��', $subject);  // subject�β��Ԥ򥹥ڡ������ִ���
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $sponsor    = $request->get('sponsor');             // ��ż�
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����)
        $atten_num  = count($atten);                        // ���ʼԿ�
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $mail       = $request->get('mail');                // �᡼������� Y/N
        ///// ������������� 2006/07/24 ADD
        $week = array('��', '��', '��', '��', '��', '��', '��');
        $dayWeek = $week[date('w', mktime(0, 0, 0, $month, $day, $year))];
        // ��żԤ�̾�������
        if (!$this->getSponsorName($sponsor, $res)) {
            $_SESSION['s_sysmsg'] = "�᡼�����Ǽ�żԤ�̾�������Ĥ���ޤ��� [ $sponsor ]";
        } else {
            $sponsor_name = $res[0][0];
            $sponsor_addr = $res[0][1];
            // ��ļ�̾�μ���
            $room_name = $this->getRoomName($room_no);
            // ���ʼԤ�̾������ (�������Ĥ���������)
            $this->getAttendanceName($atten, $atten_name, $flag);
            // ���ʼԤΥ᡼�륢�ɥ쥹�μ����ȥ᡼������
            for ($i=0; $i<$atten_num; $i++) {
                if ($flag[$i] == 'NG') continue;
                // ���ʼԤΥ᡼�륢�ɥ쥹����
                if ( !($atten_addr=$this->getAttendanceAddr($atten[$i])) ) {
                    continue;
                }
                $to_addres = $atten_addr;
                $message  = "���ΰ���� {$sponsor_name} ���󤬽��ʼԤ˥᡼������Ф�����ˤ��������������줿��ΤǤ���\n\n";
                $message .= "{$subject}\n\n";
                if ($cancel) {
                    $message .= "�����β��(�ǹ礻)��{$this->getUserName()}����ˤ�ꥭ��󥻥�(���)����ޤ����Τǡ���Ϣ���פ��ޤ���\n\n";
                } else {
                    $message .= "�����������ǹԤ��ޤ��Τǡ������ʤ��ꤤ�פ��ޤ���\n\n";
                }
                $message .= "                               ��\n\n";
                $message .= "��. ��������{$year}ǯ {$month}�� {$day}��({$dayWeek})\n\n";
                $message .= "��. �����֡�{$str_time} �� {$end_time}\n\n";
                $message .= "��. �졡�ꡧ{$room_name}\n\n";
                $message .= "��. ��żԡ�{$sponsor_name}\n\n";
                $message .= "��. ���ʼԡ�{$this->getAttendanceNameList($atten, $atten_name)}";
                $message .= "\n\n";
                $message .= "��. ���̾��{$subject2}\n\n";
                $message .= "�ʾ塢���������ꤤ�פ��ޤ���\n\n";
                $add_head = "From: {$sponsor_addr}\r\nReply-To: {$sponsor_addr}";
                $attenSubject = '���衧 ' . $atten_name[$i] . ' �͡� ' . $subject3;
                if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                    // ���ʼԤؤΥ᡼�������������¸
                    $this->setAttendanceMailHistory($serial_no, $atten[$i]);
                }
                ///// Debug
                if ($cancel) {
                    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                }
            }
            return true;
        }
        return false;
    }
    
    ////////// ���ʼԥ��롼�פ���Ͽ (�¹���)
    protected function groupInsert($group_no, $group_name, $atten, $owner)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
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
    
    ////////// ���ʼԥ��롼�פ��ѹ� (�¹���)
    protected function groupUpdate($group_no, $group_name, $atten, $owner)
    {
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        $update_sql = '';
        $update_sql .= "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
            ;
        "; 
        $cnt = count($atten);
        ///// ͭ����̵���� active ���ѹ����� ���ͭ���Ȥʤ�
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
    
    ////////// ���ʼԥ��롼�פκ�� (�¹���)
    protected function groupDelete($group_no)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        // �����SQLʸ������
        $delete_sql = "
            DELETE FROM meeting_mail_group WHERE group_no={$group_no}
        ";
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// ��Ĥν�ʣ�����å�(��ļ��ν�ʣ�����å����꤬����Ƥ����Τ���)
    // string $str_timestamp=���ϻ���(DB��TIMESTAMP��), string $end_time=��λ����(DB��TIMESTAMP��),
    // int $room=��ļ��ֹ�, [int $serial_no=�ѹ����θ��ǡ�����Ϣ��]
    private function duplicateCheck($str_timestamp, $end_timestamp, $room_no, $serial_no=0)
    {
        // �ǡ����ѹ����θ��ǡ����ν�������
        $deselect = "AND serial_no != {$serial_no}";
        // ��ļ��ޥ������ǽ�ʣ�����å��ˤʤäƤ��뤫��
        $query = "
            SELECT duplicate FROM meeting_room_master WHERE room_no={$room_no}
        ";
        if ($this->getUniResult($query, $duplicate) <= 0) {
            return true;
        } else {
            if ($duplicate == 'f') return true;
        }

        $no_mi_so_all = 23;   // ���ļ�����/��/���
        $no_mi_multi  = 24;   // ���ļ�����/��/  ��
        $mi_so_multi  = 25;   // ���ļ���  /��/���
        $north_only   = 20;     // ���ļ�����/  /  ��
        $middle_only  = 21;     // ���ļ���  /��/  ��
        $south_only   = 22;     // ���ļ���  /  /���
        $where_room = "(room_no = $room_no";
        switch ($room_no) {
            case $no_mi_so_all: // ���ļ�����/��/���
                $where_room .= " OR room_no = $no_mi_multi OR room_no = $mi_so_multi OR room_no = $north_only OR room_no = $middle_only OR room_no = $south_only) ";
                break;
            case $no_mi_multi:  // ���ļ�����/��/  ��
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $mi_so_multi OR room_no = $north_only OR room_no = $middle_only ) ";
                break;
            case $mi_so_multi:  // ���ļ���  /��/���
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi OR room_no = $middle_only OR room_no = $south_only) ";
                break;
            case $north_only:   // ���ļ�����/  /  ��
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi) ";
                break;
            case $middle_only:  // ���ļ���  /��/  ��
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $no_mi_multi OR room_no = $mi_so_multi) ";
                break;
            case $south_only:   // ���ļ���  /  /���
                $where_room .= " OR room_no = $no_mi_so_all OR room_no = $mi_so_multi) ";
                break;
            default:            // ����ʳ�
                $where_room .= ") ";
                break;
        }

        // ���ϻ��֤ν�ʣ�����å�
        $chk_sql1 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$str_timestamp}'
            AND end_time > '{$str_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        // ��λ���֤ν�ʣ�����å�
        $chk_sql2 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$end_timestamp}'
            AND end_time > '{$end_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        // ���Τν�ʣ�����å�
        $chk_sql3 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time >= '{$str_timestamp}'
            AND end_time <= '{$end_timestamp}'
            AND {$where_room}
            {$deselect}
            limit 1
        ";
        if ($this->getUniResult($chk_sql1, $check) > 0) {           // ���ϻ��֤ν�ʣ�����å�
            $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $_SESSION['s_sysmsg'] = "���ϻ��֤�����{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        } elseif ($this->getUniResult($chk_sql2, $check) > 0) {     // ��λ���֤ν�ʣ�����å�
            $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $_SESSION['s_sysmsg'] = "��λ���֤�����{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        } elseif ($this->getUniResult($chk_sql3, $check) > 0) {     // ���Τν�ʣ�����å�
            $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
            $_SESSION['s_sysmsg'] = "��{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
            return false;
        } else {
            return true;    // ��ʣ�ʤ�
        }
    }
    
    ////////// ���Ѽ֤ν�ʣ�����å�(���Ѽ֤ν�ʣ�����å����꤬����Ƥ����Τ���)
    // string $str_timestamp=���ϻ���(DB��TIMESTAMP��), string $end_time=��λ����(DB��TIMESTAMP��),
    // int $car=���Ѽ��ֹ�, [int $serial_no=�ѹ����θ��ǡ�����Ϣ��]
    private function cardupCheck($str_timestamp, $end_timestamp, $car_no, $serial_no=0)
    {
        if ($car_no !='') {
            // �ǡ����ѹ����θ��ǡ����ν�������
            $deselect = "AND serial_no != {$serial_no}";
            // ���Ѽ֥ޥ������ǽ�ʣ�����å��ˤʤäƤ��뤫��
            $query = "
                SELECT duplicate FROM meeting_car_master WHERE car_no={$car_no}
            ";
            if ($this->getUniResult($query, $car_dup) <= 0) {
                return true;
            } else {
                if ($car_dup == 'f') return true;
            }
            // ���ϻ��֤ν�ʣ�����å�
            $chk_sql1 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time < '{$str_timestamp}'
                AND end_time > '{$str_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            // ��λ���֤ν�ʣ�����å�
            $chk_sql2 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time < '{$end_timestamp}'
                AND end_time > '{$end_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            // ���Τν�ʣ�����å�
            $chk_sql3 = "
                SELECT subject FROM meeting_schedule_header
                WHERE str_time >= '{$str_timestamp}'
                AND end_time <= '{$end_timestamp}'
                AND car_no = {$car_no}
                {$deselect}
                limit 1
            ";
            if ($this->getUniResult($chk_sql1, $check) > 0) {           // ���ϻ��֤ν�ʣ�����å�
                $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $_SESSION['s_sysmsg'] = "���ϻ��֤�����{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
                return false;
            } elseif ($this->getUniResult($chk_sql2, $check) > 0) {     // ��λ���֤ν�ʣ�����å�
                $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $_SESSION['s_sysmsg'] = "��λ���֤�����{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
                return false;
            } elseif ($this->getUniResult($chk_sql3, $check) > 0) {     // ���Τν�ʣ�����å�
                $check = str_replace("\r", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $check = str_replace("\n", '��', $check);               // ��̾�β��Ԥ򥹥ڡ������Ѵ�
                $_SESSION['s_sysmsg'] = "��{$check}�ס��Ƚ�ʣ���Ƥ��ޤ���";
                return false;
            } else {
                return true;    // ��ʣ�ʤ�
            }
        } else {
            return true;    // ��Ͽ̵��
        }
    }
    
    ////////// ��ĥ������塼��μ¹��� �ɲ�
    private function add_execute($request)
    {
        ///// �ѥ�᡼������ʬ��
        $year       = $request->get('yearReg');             // ���ͽ���ǯ����
        $month      = $request->get('monthReg');            // ���ͽ��η��
        $day        = $request->get('dayReg');              // ���ͽ���������
        $subject    = $request->get('subject');             // ��ķ�̾
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $sponsor    = $request->get('sponsor');             // ��ż�
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����)
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $car_no     = $request->get('car_no');              // ���Ѽ��ֹ�
        $mail       = $request->get('mail');                // �᡼������� Y/N
        // �᡼������ Y/N �� boolean�����Ѵ�
        if ($mail == 't') $mail = 'TRUE'; else $mail = 'FALSE';
        // ������ last_date last_host ����Ͽ�����������
        // regdate=��ư��Ͽ
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // ���ʼԤοͿ������
        $atten_num = count($atten);
        $insert_qry = "
            INSERT INTO meeting_schedule_header
            (subject, str_time, end_time, room_no, sponsor, atten_num, mail, last_date, last_host, car_no)
            VALUES
            ('$subject', '{$year}-{$month}-{$day} {$str_time}', '{$year}-{$month}-{$day} {$end_time}', $room_no, '$sponsor', $atten_num, $mail, '$last_date', '$last_host', $car_no)
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $insert_qry .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ((SELECT max(serial_no) FROM meeting_schedule_header), '{$atten[$i]}', FALSE)
                ;
            ";
        }
        if ($this->execute_Insert($insert_qry)) {
            $query = "SELECT max(serial_no) FROM meeting_schedule_header";
            $serial_no = false;     // �����
            $this->getUniResult($query, $serial_no);
            return $serial_no;      // ��Ͽ�������ꥢ���ֹ���֤�
        } else {
            return false;
        }
    }
    
    ////////// ��ĥ������塼��μ¹��� ���(����)
    private function del_execute($serial_no, $subject)
    {
        // ��¸�Ѥ�SQLʸ������
        $save_sql   = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        $delete_sql = "
            DELETE FROM meeting_schedule_header WHERE serial_no={$serial_no}
            ;
        ";
        $delete_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Delete($delete_sql, $save_sql);
    }
    
    ////////// ��ĥ������塼��μ¹��� �ѹ�
    private function edit_execute($request)
    {
        ///// �ѥ�᡼������ʬ��
        $serial_no  = $request->get('serial_no');           // Ϣ��(�����ե������)
        $year       = $request->get('yearReg');             // ���ͽ���ǯ����
        $month      = $request->get('monthReg');            // ���ͽ��η��
        $day        = $request->get('dayReg');              // ���ͽ���������
        $subject    = $request->get('subject');             // ��ķ�̾
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $sponsor    = $request->get('sponsor');             // ��ż�
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����)
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $car_no     = $request->get('car_no');              // ���Ѽ��ֹ�
        $mail       = $request->get('mail');                // �᡼������� Y/N
        // ��¸�Ѥ�SQLʸ������
        $save_sql = "
            SELECT * FROM meeting_schedule_header WHERE serial_no={$serial_no}
        ";
        // ������ last_date last_host ����Ͽ�����������
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // ���ʼԤοͿ������
        $atten_num = count($atten);
        $update_sql = "
            UPDATE meeting_schedule_header SET
            subject='{$subject}', str_time='{$year}-{$month}-{$day} {$str_time}', end_time='{$year}-{$month}-{$day} {$end_time}',
            room_no={$room_no}, sponsor='{$sponsor}', atten_num='{$atten_num}', mail='{$mail}',
            last_date='{$last_date}', last_host='{$last_host}', car_no='{$car_no}'
            where serial_no={$serial_no}
            ;
        "; 
        $update_sql .= "
            DELETE FROM meeting_schedule_attendance WHERE serial_no={$serial_no}
            ;
        ";
        for ($i=0; $i<$atten_num; $i++) {
            $update_sql .= "
                INSERT INTO meeting_schedule_attendance
                (serial_no, atten, mail)
                VALUES
                ({$serial_no}, '{$atten[$i]}', FALSE)
                ;
            ";
        }
        // $save_sql�ϥ��ץ����ʤΤǻ��ꤷ�ʤ��Ƥ��ɤ�
        return $this->execute_Update($update_sql, $save_sql);
    }
    
    ////////// ��żԤ�̾�������
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
        $res = array();     // �����
        if ($this->getResult2($query, $res) < 1) {
            return false;
        } else {
            return true;
        }
    }
    
    ////////// ��ļ�̾�μ���
    private function getRoomName($room_no)
    {
        $query = "
            SELECT trim(room_name) FROM meeting_room_master WHERE room_no={$room_no}
        ";
        $room_name = '';    // �����
        $this->getUniResult($query, $room_name);
        return $room_name;
    }
    
    ////////// ���Ѽ�̾�μ���
    private function getCarName($room_no)
    {
        $query = "
            SELECT trim(car_name) FROM meeting_car_master WHERE car_no={$car_no}
        ";
        $car_name = '';    // �����
        $this->getUniResult($query, $car_name);
        return $car_name;
    }
    
    ////////// ���ʼԤ�̾������
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
                $_SESSION['s_sysmsg'] .= "�᡼�����ǽ��ʼԤ�̾�������Ĥ���ޤ��� [ {$atten[$i]} ]";
                $flag[$i] = 'NG';
            } else {
                $flag[$i] = 'OK';
            }
        }
    }
    
    ////////// ���ʼԤΥ᡼�륢�ɥ쥹����
    private function getAttendanceAddr($atten)
    {
        $query = "
            SELECT trim(mailaddr) FROM user_master WHERE uid = '{$atten}'
        ";
        $atten_addr = '';
        if ($this->getUniResult($query, $atten_addr) < 1) {
            $_SESSION['s_sysmsg'] .= "�᡼�����ǽ��ʼԤΥ᡼�륢�ɥ쥹�����Ĥ���ޤ��� [ {$atten} ]";
        }
        return $atten_addr;
    }
    
    ////////// ���ʼԤ�̾����᡼��˺ܤ��뤿��ʸ����ǰ�����
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
    
    ////////// ���ʼԤؤΥ᡼�������������¸
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
    
    ////////// �ײ�ͭ����Ͽ�κ��
    public function hdelete($request)
    {
        ///// �ѥ�᡼������ʬ��
        $uid        = $request->get('uid_no');           // �Ұ��ֹ�
        $acq_date   = $request->get('acq_date');         // ������
        // �оݷײ�ͭ���¸�ߥ����å�
        $chk_sql = "
            SELECT uid FROM user_holyday WHERE uid='$uid' and acq_date='$acq_date'
        ";
        if ($this->getUniResult($chk_sql, $check) < 1) {     // ����ηײ�ͭ���¸�ߥ����å�
            $_SESSION['s_sysmsg'] = "�оݤηײ�ͭ��Ϥ���ޤ���";
        } else {
            $query="DELETE FROM user_holyday WHERE uid='$uid' and acq_date='$acq_date'";
            if ($this->getUniResult($query, $rows) < 1) {     // ����ηײ�ͭ���¸�ߥ����å�
                $_SESSION['s_sysmsg'] = '����ηײ�ͭ��������ޤ�����';
                return true;
            } else {
                $_SESSION['s_sysmsg'] = '����Ǥ��ޤ���Ǥ�����';
            }
        }
        return false;
    }

    ////////// �Ժ�ͽ��μ���
    public function getViewAbsence(&$result)
    {
        $query = sprintf( "
            SELECT admit_status, sm.section_name, ud.name, content, start_time, end_time
            FROM                sougou_deteils
            LEFT OUTER JOIN     user_detailes   AS ud    USING(uid)
            LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
            LEFT OUTER JOIN     section_master  AS sm    USING(sid)
            LEFT OUTER JOIN     act_table       AS at    USING(act_id)
            {$this->where}
            ORDER BY ct.orga_id ASC, start_date ASC, start_time ASC, end_time
        ");
        $res = array();
        if( ($rows=$this->getResult2($query, $res)) < 1 ) {
            return false;
        }
        $result->add_array($res);
        return $rows;
    }

    ////////// ����UID�ϡ�����Ϥξ�ǧ�ԡʹ���Ĺ�ˤǤ�����
    public function IsSogoAdmitrKo($uid)
    {
        $query = "SELECT kojyotyo FROM approval_path_master_late WHERE standards_date<now() ORDER BY standards_date DESC LIMIT 1";
        $res = array();
        if( getResult2($query, $res) <= 0 ) return false;
        if( $res[0][0] == $uid ) return true;   // ��ǧ�ԡʹ���Ĺ�ˤǤ���
        return false;   // ��ǧ�ԡʹ���Ĺ�ˤǤϤʤ���
    }

    ////////// ����UID�ϡ�����Ϥξ�ǧ�ԤǤ�����
    public function IsSogoAdmitr($uid)
    {
        $post = array("kakarityo", "katyo", "butyo");
        
        for( $n=0; $n<3; $n++ ) {
            $query = "SELECT act_id FROM approval_path_master WHERE {$post[$n]}='$uid' LIMIT 1";
            $res = array();
            if( getResult2($query, $res) > 0 ) return true; // ��ǧ�ԤǤ���
        }
        return false;   // ��ǧ�ԤǤϤʤ���
    }

    ////////// ����UID��̤��ǧ����Ϸ������
    public function getSougouAdmitCnt($uid)
    {
        $query = "SELECT count(*) FROM sougou_deteils where admit_status='$uid'";
        $res = array();
        $cnt = getResult2($query, $res);
        if( $cnt > 0 ) {
            return $res[0][0];
        } else {
            return 0;
        }
    }

    ////////// ����UID��̾������
    public function getUidName($uid)
    {
        $query = "SELECT trim(name) FROM user_detailes WHERE uid = '$uid'";
        if ($this->getUniResult($query, $UidName) < 1) {
            return "�߼����Բ�";
        }
        return $UidName;
    }

    ////////// ��������/UID���Ժ���ͳ����
    // ����UID���ԺߤǤ�����
    public function getAbsence($uid)
    {
        $date  = date('Ymd');  // ����ǯ����
        $query = "
                    SELECT absence, str_time, end_time FROM working_hours_report_data_new
                    WHERE uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                 ";
        $res = array();
        if( $this->getResult2($query, $res) <= 0 ) {
            return "<font style='background-color:blue; color:white;'>�ж�</font>";
        }
        return $this->getAbsenceReason($res);
    }

    ////////// �Ժ���ͳ����
    public function getAbsenceReason($res)
    {
        $state = "";
        switch ($res[0][0]) {
            case '11': $state = "ͭ��"; break;
            case '12': $state = "���"; break;
            case '13': $state = "̵��"; break;
            case '14': $state = "��ĥ"; break;
            case '15': $state = "����"; break;
            case '16': $state = "�õ�"; break;
            case '17': $state = "�Ļ�"; break;
            case '18': $state = "Ĥ��"; break;
            case '19': $state = "����"; break;
            case '20': $state = "���"; break;
            case '21': $state = "����"; break;
            case '22': $state = "�ٿ�"; break;
            case '23': $state = "ϫ��"; break;
            default  : break;
        }
        if($state) return "<font style='background-color:red; color:white;'>{$state}</font>";
        
        if($res[0][1] == '0000') $state = "�Ժ�";
        if($res[0][2] != '0000') $state = "���";
        
        if($state) return "<font style='background-color:red; color:white;'>{$state}</font>";
        
        return "<font style='background-color:red; color:white;'>����</font>";
    }

} // Class MeetingSchedule_Model End

?>
