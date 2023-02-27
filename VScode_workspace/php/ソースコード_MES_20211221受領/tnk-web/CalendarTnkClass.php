<?
//////////////////////////////////////////////////////////////////////////////
// �������칩�參������ �桼�������󥿡��ե����� ���饹 ������DB������  //
//                          �ǡ��������������֥������ȥ��饹���ĥ���Ƥ���  //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/19 Created   CalendarTnkClass.php                                //
//  Ver1.00   DB(company_holiday, assembly_holiday, ...)�����������      //
// 2006/06/26 �ǡ����١����Υơ��֥���ѹ� (���Ƥ����˵�������Ư��������)   //
//  Ver1.10   company_holiday �� company_calendar (�����Ȥ����Ƥ������б�)//
//            show_calendar()�᥽�åɤ� echo ���Ϥ��� return ���Ϥ��ѹ�     //
// 2006/06/29 setAllLinkYMD()�᥽�åɤ� $url �� onClick=''�Ѥ� JavaScript   //
//  Ver1.11   �᥽�åɤ���ꤹ�� $url="location.replace(\"?????\")" Ajax�б�//
// 2006/07/01 show_calendar()�� <td> �� <td nowrap> ���ɲ�                  //
//  Ver1.12                                                                 //
// 2006/07/06 �ץ�ѥƥ���(���С�)�� public �� protected ���ѹ�           //
//  Ver1.13   setUserHoliday()�᥽�åɤΰ������å��ѹ� protected��private //
// 2006/07/10 CSS��classOnMouseOver,weekClass���ɲä�HTML����������style���//
//  Ver1.14   calendar.css �� Ver1.14 ����Ѥ��뤳��                        //
// 2007/02/06 �ߤɤ���������¤������Ѥ������ɲá���̱���������ߤɤ����//
//  Ver1.15   �ξ����ɲá����ص����������������Υ����å��ɲ�(��Ϣ�٤ޤ�)  //
// 2010/08/23 2011ǯ1��5������ưŪ�˵ٶȤˤʤäƤ��ޤ���Ĵ��           ��ë //
// 2019/11/14 21���ε٤ߤ��б�(���ݡ��Ĥ��������������ΰ������)       ��ë //
// 2021/10/28 �˺�����̾��������̾�Τˡʥ��ݡ��Ĥ��������ɤ��������   ��ë //
//////////////////////////////////////////////////////////////////////////////
if (class_exists('CalendarTNK')) {
    return;
}
// require_once ('CalendarClass.php');             // ���쥫���������饹
require_once ('daoInterfaceClass.php');         // TNK ������ DAO���󥿡��ե��������饹
define('CalendarTNK_VERSION', '1.15');

/********************************************************************************
*             CalendarTNK (base class) ����(��̳���)���饹                     *
********************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class CalendarTNK extends daoInterfaceClass
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
        // parent::__construct($startWeek, $dsplayFlg);
        //return;
        
        // ����������0-����, 6-���ˡ�
        $this->wfrom = $startWeek;
        
        // ����ʳ������դ�ɽ�����뤫�ɤ�����0-ɽ�����ʤ� 1-ɽ�������
        $this->beforeandafterday = $dsplayFlg;
        
        // �������Ф����طʿ��������0-ʿ��, 1-��, 2-������, 3-����ʳ���ʿ��, 4-������
        // $this->cssClass = array('#eeeeee', '#ccffff', '#ffcccc', '#ffffff', '#ffffcc', 'yellow');
        $this->cssClass = array(" class='class0'", " class='class1'", " class='class2'", " class='class3'", " class='class4'", " class='class5'", " class='class6'");
        $this->kindTitle = array(1, 0, 0, 0, 0, 0, 1);  // ��� array(2, 0, 0, 0, 0, 0, 1) ���ڤ�Ʊ�����ˤ��ƻ��������Ĵ
        $this->kind      = array(0, 0, 0, 0, 0, 0, 0);  // ��� array(2, 0, 0, 0, 0, 0, 1)
        
        // ������̾��
        $this->week = array('��', '��', '��', '��', '��', '��', '��');
    }
    
    // ************************************************************************** //
    // * ���ꤵ�줿���Ƥǥ���������ɽ�����롣                                 * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (���������ơ��֥����)                      * //
    // ************************************************************************** //
    public function show_calendar($year, $month, $day=0)
    {
        // �����λ���
        if (!isset($this->userHoliday)) $this->setUserHoliday($year, $month);
        
        // �¹�
        return $this->showCalendarExecute($year, $month, $day);
    }
    
    // ************************************************************************** //
    // * ���ꤵ�줿�����Ф��ƥ�󥯤����ꤹ�롣���̥��                       * //
    // * @param int $day                                                        * //
    // * @param string $url                                                     * //
    // * @param string $title               ���åץإ��ɽ��                    * //
    // * @param string $status  default=''  ���ơ������С�ɽ��                  * //
    // * @return void                                                           * //
    // ************************************************************************** //
    public function set_link($day, $url, $title, $status='')
    {
        $this->link[$day]['url'] = $url;
        $this->link[$day]['title'] = $title;
        if ($status == '') {
            $this->link[$day]['status'] = '�����򥯥�å�����л��ꤵ�줿����������Ԥ��ޤ���';
        } else {
            $this->link[$day]['status'] = $status;
        }
        $this->link[$tday]['id'] = sprintf('%02d', $day);
    }
    
    // ************************************************************************** //
    // * ���Ƥ������Ф��ƥ�󥯤����ꤹ�롣                                     * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param string $url                                                     * //
    // * @param string $title  default=''   ���åץإ�פȥ��ơ������С�ɽ��    * //
    // * @return void                                                           * //
    // ************************************************************************** //
    public function setAllLinkYMD($year, $month, $url, $title='')
    {
        ///// Ver1.11���� $url �� onClick=''�Ѥ� JavaScript�Υ᥽�åɤ���ꤹ��
        $uniq = uniqid();
        // $tday�����η��������Ķ����ޤǥ롼��
        $tday = 1;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        while ($tday <= $mday) {
            if (preg_match('/\?/', $url)) {
                $url_para = "&year={$year}&month={$month}&day={$tday}&id={$uniq}";
            } else {
                $url_para = "?year={$year}&month={$month}&day={$tday}&id={$uniq}";
            }
            if (preg_match('/%s/', $url)) {
                $urlParaAdd = sprintf($url, $url_para);
            } else {
                $urlParaAdd = $url;
            }
            $this->link[$tday]['url']    = $urlParaAdd;
            $this->link[$tday]['title']  = "{$year}ǯ {$month}�� {$tday}�� {$title}";
            $this->link[$tday]['status'] = $this->link[$tday]['title'];
            $this->link[$tday]['id']     = sprintf('%4d%02d%02d', $year, $month, $tday);
            $tday++;
        }
    }
    
    // ************************************************************************** //
    // * �������ꤵ��Ƥ����󥯤����Ʋ�����롣                               * //
    // * @return void                                                           * //
    // ************************************************************************** //
    public function clear_link()
    {
        $this->link = array();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    // ************************************************************************** //
    // * ���ꤵ�줿���Ƥǥ���������ɽ�����롣�¹ԥ��å�                     * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @param int $day                                                        * //
    // * @return string $calendar (���������ơ��֥����)                      * //
    // ************************************************************************** //
    protected function showCalendarExecute($year, $month, $day)
    {
        // ���η�γ��ϤȤ�����ͤ����
        $from = 1;
        while (date('w', mktime(0, 0, 0, $month, $from, $year)) != $this->wfrom) {
            $from--;
        }
        // ����ȼ����ǯ������
        list($ny, $nm, $nj) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month+1, 1, $year)));
        list($by, $bm, $bj) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month-1, 1, $year)));
        // ��������
        $arr = getdate();
        // ɽ������
        $calendar = "\n";   // �����
        $calendar .= "<table class='calendar' summary='��������'>\n";
        $calendar .= "<tr>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            $calendar .= "<th class='currentTitle' colspan='7'>\n";
            $calendar .= $year . 'ǯ' . $month . "��\n";
            $calendar .= "&nbsp;����{$arr['mday']}��\n";
        } else {
            $calendar .= "<th class='title' colspan='7'>\n";
            $calendar .= $year . 'ǯ' . $month . "��\n";
        }
        $calendar .= "</th>\n";
        $calendar .= "</tr>\n";
        // ����ɽ��
        $calendar .= "<tr class='weekClass'>\n";
        for ($i=0; $i<7; $i++) {
            $wk = ($this->wfrom + $i) % 7;
            $calendar .= '<td nowrap' . $this->cssClass[$this->kindTitle[$wk]] . ">" . $this->week[$wk] . "</td>\n";
        }
        $calendar .= "</tr>\n";
        // $tday�����η��������Ķ����ޤǥ롼��
        $tday = $from;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        $wnum = 0;  // ���ֹ�
        while ($tday <= $mday) {
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $wk = ($this->wfrom + $i) % 7;
                $cssClass = $this->cssClass[$this->kind[$wk]];
                /*
                if ($year == 2020 && $month == 12 && $tday == 26) {
                    $cssClass = " class='class0'";
                }
                */
                // ����Ƚ��
                if ($tday >= 1 && $tday <= $mday) {
                    if ($arr['year'] == $year && $arr['mon'] == $month && $arr['mday'] == $tday) {
                        // ����
                        if ($this->userHoliday[$tday] != 1) {
                            $cssClass = $this->cssClass[4];   // ����
                        } else {
                            $cssClass = $this->cssClass[6];   // �����ȵ�������ʣ�������
                        }
                    } else if ($this->userHoliday[$tday] == 1) {    // (�쥿���פ�@$this->holiday[$tday] == 1)
                        // ����(�桼��������)
                        $cssClass = $this->cssClass[2];
                    }
                    // ������
                    if ($day == $tday) {    // $day ��0�����ꤵ��Ƥ����̵��
                        if ($this->userHoliday[$tday] != 1) {
                            $cssClass = $this->cssClass[5];   // ������(����å�������)
                        } else {
                            $cssClass = $this->cssClass[6];   // �������ȵ�������ʣ�������
                        }
                    }
                } else {
                    // if ($wk > 0 && $wk < 6)���ꥸ�ʥ� �� �����ѹ���������ʳ������Ƥ���
                    if ($wk >= 0 && $wk <= 6) $cssClass = $this->cssClass[3];
                }
                list($lyear, $lmonth, $lday) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month, $tday, $year)));
                // �ǡ�����ʬɽ��
                if (($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
                    if (isset($this->link[$tday])) {
                        if ($this->userHolidayName[$tday] != '') {
                            $title  = "{$this->link[$tday]['title']} {$this->userHolidayName[$tday]}";
                            $status = "{$this->link[$tday]['status']} {$this->userHolidayName[$tday]}";
                        } else {
                            $title  = "{$this->link[$tday]['title']}";
                            $status = "{$this->link[$tday]['status']}";
                        }
                        $className = str_replace(' class=', '', $cssClass);
                        $className = str_replace("'", '', $className);
                        $calendar .= "<td nowrap{$cssClass}\n";
                        $calendar .= "    onClick='{$this->link[$tday]['url']}; return false;'\n";
                        $calendar .= "    title='{$title}'\n";
                        $calendar .= "    onMouseover=\"this.className='classOnMouseOver'; status='{$status}';return true;\"\n";
                        $calendar .= "    onMouseout =\"this.className='{$className}'; status=''\"\n";
                        $calendar .= "    id='{$this->link[$tday]['id']}'\n";
                        $calendar .= ">\n";
                        $calendar .= "    <label for='{$this->link[$tday]['id']}'>{$lday}</label>\n";
                        $calendar .= "</td>\n";
                    } else {
                        $calendar .= "<td nowrap{$cssClass}>\n";
                        $calendar .= "    {$lday}\n";
                        $calendar .= "</td>\n";
                    }
                } else {
                    $calendar .= "<td nowrap{$cssClass}>\n";
                    $calendar .= "    &nbsp;\n";
                    $calendar .= "</td>\n";
                }
                $tday++;
            }
            $calendar .= "</tr>\n"; 
            $wnum++;
        }
        switch ($wnum) {
        case 4;
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            break;
        case 5;
            $calendar .= "<tr>\n";
            for ($i=0; $i<7; $i++) {
                $calendar .= "<td nowrap class='class3'>&nbsp;</td>\n";
            }
            $calendar .= "</tr>\n";
            break;
        }
        $calendar .= "</table>\n";
        return $calendar;
    }
    
    /***************************************************************************
    *                              Private methods                             *
    ***************************************************************************/
    // ************************************************************************** //
    // * �����η׻���Ԥ���(����̾�⥻�åȤ���)  �桼�������ꥫ������������ * //
    // * @param int $year                                                       * //
    // * @param int $month                                                      * //
    // * @return void                                                           * //
    // ************************************************************************** //
    private function setUserHoliday($year, $month)
    {
        $endMonth = $month + 1;
        $endYear  = $year;
        if ($endMonth == 13) {
            $endMonth = '01';
            $endYear++;
        }
        // DB����Ҥε����ǡ��������
        $query = "
            SELECT tdate FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
        ";
        if (($rows = $this->getResult2($query, $check)) <= 0) {
            ///// ��Ͽ̵��holiday�Υǡ�������Ѥ���DB����������
            $this->initCalendarFormat($year, $month);
        }
        // DB�κ��ɹ��� ɬ�פʥ������������
        $query = "
            SELECT to_char(tdate, 'DD'), note, bd_flg FROM company_calendar
            WHERE tdate >= DATE '{$year}-{$month}-01' AND tdate < DATE '{$endYear}-{$endMonth}-01'
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
    private function initCalendarFormat($year, $month)
    {
        if (!isset($this->holiday)) $this->setHoliday($year, $month);
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        while (1) {
            $day = date('j', $timestamp);               // ��Ƭ��0���դ��ʤ�(1-31)
            if (isset($this->holiday[$day])) {
                $bd_flg = 'FALSE';                      // �����˥��å�
                $note   = $this->holidayName[$day];     // ����̾�򥻥å�
            } else {
                $bd_flg = 'TRUE';                       // �Ķ����˥��å�
                $note   = '';
            }
            if (date('w', $timestamp) == 0 || date('w', $timestamp) == 6) { // 0=���ˤ�6(����)�ϵ���
                $bd_flg = 'FALSE';                      // �����˥��å�
            }
            $insert = "
                INSERT INTO company_calendar (tdate, bd_flg, note, last_date, last_user)
                VALUES (DATE '{$year}-{$month}-{$day}', {$bd_flg}, '{$note}', '{$last_date}', '{$last_user}')
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
        // ���η�κǽ�η��������������򻻽�
        $day = 1;
        while (date('w', mktime(0 ,0 ,0 , $month, $day, $year)) <> 1) {
            $day++;
        }
        // �����򥻥å�
        switch ($month) {
        case 1:
            // ��ö
            $this->holiday[1] = 1;
            $this->holidayName[1] = '��ö';
            // ���ͤ���
            if ($year < 2000) {
                $this->holiday[15] = 1;
                $this->holidayName[15] = '���ͤ���';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holidayName[$day+7] = '���ͤ���';
            }
            // ��Ҥεٶ����򥻥å�
            for ($i=2; $i<=15; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if($year==2011 && $i==4) {
                    } elseif($year==2023 && $i==2) {
                    } else {  
                        if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                            if (day_off($timestamp)) {
                                $this->holiday[$i] = 1;
                                $this->holidayName[$i] = 'ǯ�ϵٲ�';
                            }
                        }
                    }
                }
            }
            break;
        case 2:
            // ����ǰ����
            $this->holiday[11] = 1;
            $this->holidayName[11] = '����ǰ����';
            // ŷ��������
            if ($year > 2019) {
                $this->holiday[23] = 1;
                $this->holidayName[23] = 'ŷ��������';
            }
            break;
        case 3:
            // ��ʬ����
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(20.8431+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holidayName[$tmp] = '��ʬ����';
            }
            break;
        case 4:
            // ŷ�������� or �ߤɤ���� or ���¤���
            $this->holiday[29] = 1;
            if ($year < 1989) {
                $this->holidayName[29] = 'ŷ��������';
            } elseif ($year < 2007) {
                $this->holidayName[29] = '�ߤɤ����';
            } else {
                $this->holidayName[29] = '���¤���';
            }
            break;
        case 5:
            // ��ˡ��ǰ��
            $this->holiday[3] = 1;
            $this->holidayName[3] = '��ˡ��ǰ��';
            
            // ���ɤ����
            $this->holiday[5] = 1;
            $this->holidayName[5] = '���ɤ����';
            break;
        case 7:
            // ������
            if ($year > 2002) {
                $this->holiday[$day+14] = 1;
                $this->holidayName[$day+14] = '������';
            } elseif($year > 1994) {
                $this->holiday[21] = 1;
                $this->holidayName[21] = '������';
            }
            if ($year == 2020) {
                $this->holiday[$day+14] = 0;
                $this->holidayName[$day+14] = '';
                $this->holiday[24] = 1;
                $this->holidayName[24] = '������';
            }
            if ($year == 2020) {
                $this->holiday[24] = 1;
                $this->holidayName[24] = '���ݡ��Ĥ���';
            }
            break;
        case 8:
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '������';
            }
            if ($year == 2020) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '';
                $this->holiday[10] = 1;
                $this->holidayName[10] = '������';
            }
            // ��Ҥεٶ����򥻥å�
            for ($i=5; $i<=26; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holidayName[$i] = '�ƴ��ٲ�';
                        }
                    }
                }
            }
           /*
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holidayName[11] = '������';
            }
            */
            break;
        case 9:
            // ��Ϸ����
            if ($year < 2003) {
                $this->holiday[15] = 1;
                $this->holidayName[15] = '��Ϸ����';
            } else {
                $this->holiday[$day+14] = 1;
                $this->holidayName[$day+14] = '��Ϸ����';
            }
            // ��ʬ����
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(23.2488+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holidayName[$tmp] = '��ʬ����';
            }
            break;
        case 10;
            // �ΰ����
            if ($year < 2000) {
                $this->holiday[10] = 1;
                $this->holidayName[10] = '�ΰ����';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holidayName[$day+7] = '���ݡ��Ĥ���';
            }
            if ($year == 2019) {
                $this->holiday[22] = 1;
                $this->holidayName[22] = '¨�������¤ε�';
            }
            
            if ($year == 2020) {
                $this->holiday[$day+7] = 0;
                $this->holidayName[$day+7] = '';
            }
            break;
        case 11:
            // ʸ������
            $this->holiday[3] = 1;
            $this->holidayName[3] = 'ʸ������';
            
            // ��ϫ���դ���
            $this->holiday[23] = 1;
            $this->holidayName[23] = '��ϫ���դ���';
            break;
        case 12:            
            // ŷ��������
            if ($year < 2019) {
                $this->holiday[23] = 1;
                $this->holidayName[23] = 'ŷ��������';
            }
            /*
            // ŷ�������� 2019ǯ����̵���ʤ�
            if ($year > 2018) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = '';
            }
            */
            // 2020ǯ�Τ�26���ж�
            /*
            if ($year == 2020) {
                $this->holiday[26] = 0;
                $this->holiday_name[26] = '';
            }
            */
            // ��Ҥεٶ����򥻥å�
            for ($i=20; $i<=31; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holidayName[$i] = 'ǯ���ٲ�';
                        }
                    }
                }
            }
            break;
        }
        
        // ��̱�ε����򥻥å�
        if ($year > 1985 && $year <= 2006) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holidayName[$i+1] = '��̱�ε���';
                    $i = $i + 3;
                }
            }
        }
        
        // �� �ߤɤ����򥻥å�
        if ($year >= 2007) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holidayName[$i+1] = '�ߤɤ����';
                    $i = $i + 3;
                }
            }
        }
        
        // ���ص����򥻥å�
        $sday = $day - 1;
        if ($sday == 0) $sday = 7;
        for ($i=$sday; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i=$i+7) {
            if (isset($this->holiday[$i]) && isset($this->holiday[$i+1]) && isset($this->holiday[$i+2])) {
                $this->holiday[$i+3] = 1;
                $this->holidayName[$i+3] = '���ص���';
            } elseif (isset($this->holiday[$i]) && isset($this->holiday[$i+1])) {
                $this->holiday[$i+2] = 1;
                $this->holidayName[$i+2] = '���ص���';
            } elseif (isset($this->holiday[$i])) {
                $this->holiday[$i+1] = 1;
                $this->holidayName[$i+1] = '���ص���';
            }
        }
    }
    
} // Class CalendarTNK End

