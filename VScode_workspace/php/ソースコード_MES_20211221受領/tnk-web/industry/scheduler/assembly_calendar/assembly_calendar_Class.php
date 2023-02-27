<?
//////////////////////////////////////////////////////////////////////////////
// ��Ω�饤��Υ������� ���饹 DB���ƥʥ󥹤�ޤ�                       //
//                     ��Ҵ��ܥ����������饹(CalendarTNK)���ĥ���Ƥ���  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/10 Created   assembly_calendar_Class.php                         //
//////////////////////////////////////////////////////////////////////////////
if (class_exists('AssemblyCalendar')) {
    return;
}
require_once ('../../../CalendarTnkClass.php');     // ���쥫���������饹

/********************************************************************************
*         AssemblyCalendarClass CalendarTNK (base class) ���쥯�饹             *
********************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyCalendar extends CalendarTNK
{
    ///// Private   properties
    ///// Protected properties
    protected $wfrom;
    protected $beforeandafterday;
    protected $link = array();
    protected $kindTitle;
    protected $kind;
    protected $cssClass;
    protected $week;
    protected $holiday;
    protected $holidayName;
    protected $userHoliday;
    protected $userHolidayName;
    ///// Public    properties
    
    /****************************************************************************
    *                               Public methods                              *
    ****************************************************************************/
    // ************************************************************************** //
    // * ���󥹥ȥ饯��                                                         * //
    // * @param int $startWeek  ��������(0=���ˡ�6=����)                        * //
    // * @param int $dsplayFlg  �оݷ�ʳ������դ�ɽ�� (0=���ʤ�, 1=����)       * //
    // * @return void                                                           * //
    // ************************************************************************** //
    ///// Constructer ����� {php5 �� __construct()} {�ǥ��ȥ饯��__destruct()}
    public function __construct($startWeek=0, $dsplayFlg=0)
    {
        ///// Constructer ���������� ���쥯�饹�� Constructer���¹Ԥ���ʤ�
        ///// ����Class��Constructer�ϥץ���ޡ�����Ǥ�ǸƽФ�
        parent::__construct($startWeek, $dsplayFlg);
        return;
    }
    
    // ************************************************************************** //
    // * ���ꤵ�줿���Ƥǥ���������ɽ�����롣                                 * //
    // * @param char(4) $line                                                   * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (���������ơ��֥����)                      * //
    // ************************************************************************** //
    public function show_calendar($line, $year, $month, $day=0)
    {
        // �����λ���
        if (!isset($this->userHoliday)) $this->setUserHoliday($line, $year, $month);
        
        // �¹�
        return $this->showCalendarExecute($year, $month, $day);
    }
    
    /***************************************************************************
    *                              Private methods                             *
    ***************************************************************************/
    // ************************************************************************** //
    // * ������η׻���Ԥ���(�����Ȥ⥻�åȤ���)��Ω�饤�󥫥����������� * //
    // * @param char(4) $line                                                   * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setUserHoliday($line, $year, $month)
    {
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DB���饤���������ǡ��������
        $query = "
            SELECT tdate FROM assembly_calendar
            WHERE line = '{$line}' AND tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        if (($rows = $this->getResult2($query, $check)) <= 0) {
            ///// ��Ͽ̵��holiday�Υǡ�������Ѥ���DB����������
            $this->initCalendarFormat($line, $year, $month);
        }
        // DB�κ��ɹ��� ɬ�פʥ������������
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM assembly_calendar
            WHERE line = '{$line}' AND tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        $res = array();
        if (($rows = $this->getResult2($query, $res)) >= 1) {
            for ($i=0; $i<$rows; $i++) {
                $key = sprintf('%d', $res[$i][0]);
                if ($res[$i][2] == 'f') {
                    $this->userHoliday[$key] = 1;
                } else {
                    $this->userHoliday[$key] = 0;
                }
                $this->userHolidayName[$key] = $res[$i][1];
            }
        }
    }
    
    // ************************************************************************** //
    // * ���������ν������Ԥ���(this->holiday�Υǡ��������)                * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function initCalendarFormat($line, $year, $month)
    {
        if (!isset($this->holiday)) $this->setHoliday($year, $month);
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        while (1) {
            $day = date('j', $timestamp);               // ��Ƭ��0���դ��ʤ�(1-31)
            if ($this->holiday[$day]) {
                $bd_flg = 'FALSE';                      // ������˥��å�
            } else {
                $bd_flg = 'TRUE';                       // �Ķ����˥��å�
                $note   = '';
            }
            $note = $this->holidayName[$day];   // �����Ȥ򥻥å�
            $insert = "
                INSERT INTO assembly_calendar (line, tdate, bd_flg, note, last_date, last_user)
                VALUES ('{$line}', DATE '{$year}-{$month}-{$day}', {$bd_flg}, '{$note}', '{$last_date}', '{$last_user}')
            ";
            if ($this->query_affected($insert) <= 0) {
                $_SESSION['s_sysmsg'] = '���������ν�����˼��Ԥ��ޤ���������ô���Ԥ�Ϣ���Ʋ�������';
                break;
            }
            $timestamp += 86400;
            if (date('m', $timestamp) != $month) break;
        }
    }
    
    // ************************************************************************** //
    // * �����η׻���Ԥ���(����̾�⥻�åȤ���)                                 * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setHoliday($year, $month)
    {
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DB����Ҵ��ܥ��������ε����ǡ��������
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
            ORDER BY tdate ASC
        ";
        $res = array();
        if (($rows = $this->getResult2($query, $res)) <= 0) {
            ///// ��Ͽ̵�� ���ѥ��顼
            $_SESSION['s_sysmsg'] = '���ܥ��������˥ǡ���������ޤ���ô���Ԥ�Ϣ���Ʋ�������';
            for ($i=0; $i<=31; $i++) {
                $this->holiday[$i] = 0;         // ���ߡ������Ʋ�Ư��
                $this->holidayName[$i] = '';     // ���ߡ�
            }
        } else {
            for ($i=0; $i<$rows; $i++) {
                $key = sprintf('%d', $res[$i][0]);
                if ($res[$i][2] == 'f') {
                    $this->holiday[$key] = 1;
                } else {
                    $this->holiday[$key] = 0;
                }
                $this->holidayName[$key] = $res[$i][1];
            }
        }
    }
    
} // Class CalendarTNK End

