<?
//////////////////////////////////////////////////////////////////////////////
// ���������δ��� ���饹 http://aki.adam.ne.jp/php/calendar/download.php  //
// �嵭���饹�Υ���������� Copyright�ε��Ҥ�̵������ʲ���ɽ�����Ƥ��ʤ�   //
// Copyright (C) 2005-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/10/31 Created   CalendarClass.php                                   //
//  Ver1.00   $this->holiday[$tday] �Υ�˥������Τ��� @���ղ�          //
// 2005/11/10 ����������<a href�����դ����ꤷ�Ƥ��뤿�᥯��å����Ѥ��� //
//  Ver1.01   �ʤ��Τ�<td>��ޤ�����ꡣ����¾</td>����ȴ���Ƥ���Τ���   //
//            1��ǯ�ϵٲ�, 12��ǯ���ٲ�, 8��ƴ��ٲ� ��set_holiday()���ɲ�  //
//            ��󥯻��� status �˽���̾�ȵٶ���̾���ɲ�                    //
// 2005/11/12 <td> ��onMouseover=,onMouseout= ���ɲä���<a href>�����Ѥ��ѹ�//
//  Ver1.02   over=this.style.color='white' onMouseout=this.style.color=''  //
// 2005/11/14 NN 7.1��<a href>������Ҥ�<td>����Ѥ���ȥ���å��������ʤ�  //
//  Ver1.03   ���� <a href>������Ū���ѻߤ�<td>�Τߤǥ��٥�Ȼ��Ѥ��ѹ����� //
// 2008/03/21 ���ص����������������Υ����å��ɲ�(��Ϣ�٤ޤ�)                //
//  Ver1.04   CalendarTnkClass.php ������                            ��ë //
// 2010/08/23 2011ǯ1��5������ưŪ�˵ٶȤˤʤäƤ��ޤ���Ĵ��           ��ë //
// 2014/01/22 2014ǯ12��23����26�������ؤȤʤ�Τ�PGM���Ĵ��          ��ë //
// 2015/06/09 2015ǯ12��23����25�������ؤȤʤ�Τ�PGM���Ĵ��          ��ë //
// 2018/12/26 20���ο�ŷ��¨������ŷ�����������ѹ����ɲ�               ��ë //
// 2019/11/14 21���ε٤ߤ��б�(���ݡ��Ĥ��������������ΰ������)       ��ë //
// 2020/11/10 22���ε٤ߤ��б�(���ݡ��Ĥ��������������ΰ������)       ��ë //
//            ����̵���������ˡ��22���ޤǤʤΤǸ������               ��ë //
// 2020/11/11 ���ΤȤ�������ˡ�ʤ��ʤΤ�22���ε٤ߤ򸵤��ᤷ��         ��ë //
// 2020/12/16 22���ε٤ߤ��б�(���ݡ��Ĥ��������������ΰ������)       ��ë //
// 2021/10/26 2023ǯ1��3������ưŪ�˵ٶȤˤʤäƤ��ޤ���Ĵ��           ��ë //
// 2021/10/28 �˺�����̾��������̾�Τˡʥ��ݡ��Ĥ��������ɤ��������   ��ë //
// 2021/11/18 ����Ӥ�23����(2022ǯ������Ĵ��4/29�жС�5/2������       ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('tnk_func.php');              // �������칩��εٶ�����ȿ�Ǥ�����

if (class_exists('Calendar')) {
    return;
}
define('Calendar_VERSION', '1.03');

/****************************************************************************
*                       base class ���ܥ��饹�����                         *
****************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Calendar
{
    ///// Private properties
    private $wfrom;
    private $beforeandafterday;
    
    private $link = array();
    private $style = array();
    
    private $kind;
    private $bgcolor;
    private $week;
    private $holiday;
    private $holiday_name;
    
    /**
     * ���󥹥ȥ饯��
     *
     * @param int $arg1
     * @param int $arg2
     * @return void
     */
    public function __construct($arg1 = 0, $arg2 = 0)
    {
        // ����������0-����, 6-���ˡ�
        $this->wfrom = $arg1;
        
        // ����ʳ������դ�ɽ�����뤫�ɤ�����0-ɽ�����ʤ� 1-ɽ�������
        $this->beforeandafterday = $arg2;
        
        // --- �ʲ���ɽ������ ---
        // �������������
        $this->style['table'] = " class='calendar'";
        $this->style['th'] = " style='background-color:#d6d3ce;'";
        $this->style['tr'] = '';
        $this->style['td'] = '';
        $this->style['tf'] = " class='tf'";
        
        // �������Ф����طʿ��������0-ʿ��, 1-��, 2-������, 3-����ʳ���ʿ��, 4-������
        $this->kind = array(2, 0, 0, 0, 0, 0, 1);
        $this->bgcolor = array('#eeeeee', '#ccffff', '#ffcccc', '#ffffff', '#ffffcc', 'yellow');
        
        // ������̾��
        $this->week = array('��', '��', '��', '��', '��', '��', '��');
        
    }
    
    /**
     * ���ꤵ�줿���Ƥǥ���������ɽ�����ޤ�
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function show_calendar($year, $month, $day = 0)
    {
        // �����λ���
        if (!isset($this->holiday)) $this->set_holiday($year, $month);
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
        echo "<table {$this->style['table']} summary='��������'>\n";
        echo "<tr>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            echo "<th style='background-color:{$this->bgcolor[4]};' colspan='7'>\n";
            echo $year . 'ǯ' . $month . "��\n";
            echo "&nbsp;����{$arr['mday']}��\n";
        } else {
            echo "<th{$this->style['th']} colspan='7'>\n";
            echo $year . 'ǯ' . $month . "��\n";
        }
        echo "</th>\n";
        echo "</tr>\n";
        // ����ɽ��
        echo '<tr' . $this->style['tr'] . " style='text-align:center;'>\n";
        for ($i=0; $i<7; $i++) {
            $wk = ($this->wfrom + $i) % 7;
            echo '<td' . $this->style['td'] . " bgcolor='" . $this->bgcolor[$this->kind[$wk]] . "'>" . $this->week[$wk] . "</td>\n";
        }
        echo "</tr>\n";
        // $tday�����η��������Ķ����ޤǥ롼��
        $tday = $from;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        $wnum = 0;  // ���ֹ�
        while ($tday <= $mday) {
            echo '<tr' . $this->style['tr'] . ">\n";
            for ($i=0; $i<7; $i++) {
                $fstyle = '';
                $wk = ($this->wfrom + $i) % 7;
                $bgcolor = $this->bgcolor[$this->kind[$wk]];
                /*
                if ($year == 2020 && $month == 12 && $tday ==26) {
                    $bgcolor = '#eeeeee';
                }
                */
                // ����Ƚ��
                if ($tday >= 1 && $tday <= $mday) {
                    if ($arr['year'] == $year && $arr['mon'] == $month && $arr['mday'] == $tday) {
                        // ����
                        $bgcolor = $this->bgcolor[4];
                    } else if (@$this->holiday[$tday] == 1) {// holiday�����åȤ���Ƥ��ʤ���礬�ۤȤ�ɤΰ١�@������
                        // ����
                        $bgcolor = $this->bgcolor[2];
                    }
                    // ������
                    if ($day == $tday) {
                        $fstyle = " style='font-weight:bold; color:red;'";  // onMouseout�������Ϲ����ˤʤ�Τ����
                        $bgcolor = $this->bgcolor[5];   // �嵭�ϥ�󥯻���ȿ�Ǥ���ʤ����ᡢ������ɲ�
                    }
                } else {
                    // ����ʳ���ʿ��
                    if ($wk > 0 && $wk < 6) $bgcolor = $this->bgcolor[3];
                }
                list($lyear, $lmonth, $lday) = explode('-', date('Y-n-j', mktime(0, 0, 0, $month, $tday, $year)));
                // �ǡ�����ʬɽ��
                if (($tday >= 1 && $tday <= $mday) || $this->beforeandafterday) {
                    if (isset($this->link[$tday])) {
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, "\n";
                        echo "    onClick='location.replace(\"{$this->link[$tday]['url']}\"); return false;'\n";
                        if (isset($this->holiday_name[$tday])) {
                        echo "    title='{$this->link[$tday]['title']} {$this->holiday_name[$tday]}'\n";
                        echo "    onMouseover=\"this.style.backgroundColor='blue';this.style.color='white'; status='{$this->link[$tday]['status']} {$this->holiday_name[$tday]}';return true;\"\n";
                        } else {
                        echo "    title='{$this->link[$tday]['title']}'\n";
                        echo "    onMouseover=\"this.style.backgroundColor='blue';this.style.color='white'; status='{$this->link[$tday]['status']}';return true;\"\n";
                        }
                        echo "    onMouseout =\"this.style.backgroundColor='';this.style.color=''; status=''\"\n";
                        echo "    id='{$this->link[$tday]['id']}'\n";
                        echo ">\n";
                        echo "    <label for='{$this->link[$tday]['id']}'>{$lday}</label>\n";
                        echo "</td>\n";
                    } else {
                        echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                        echo "    {$lday}\n";
                        echo "</td>\n";
                    }
                } else {
                    echo '<td', $this->style['td'], ' bgcolor="', $bgcolor, '"', $fstyle, ">\n";
                    echo "    &nbsp;\n";
                    echo "</td>\n";
                }
                $tday++;
            }
            echo "</tr>\n"; 
            $wnum++;
        }
        switch ($wnum) {
        case 4;
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='7'>&nbsp;</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='7'>&nbsp;</td>\n";
            echo "</tr>\n";
            break;
        case 5;
            echo "<tr>\n";
            echo "<td {$this->style['tf']} colspan='7'>&nbsp;</td>\n";
            echo "</tr>\n";
            break;
        }
        /*****
        echo "<tr>\n";
        echo '<td' . $this->style['tf'] . " colspan='7'>\n";
        if ($year == $arr['year'] && $month == $arr['mon']) {
            echo '������' . $arr['year'] . 'ǯ' . $arr['mon'] . '��' . $arr['mday'] . "��\n";
        } else {
            echo '&nbsp;';
        }
        echo "</td>\n";
        echo "</tr>\n";
        *****/
        echo "</table>\n";
    }
    
    /**
     * ���ꤵ�줿�����Ф��ƥ�󥯤����ꤷ�ޤ���
     *
     * @param int $day
     * @param string $url
     * @param string $title
     */
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
    public function setAllLinkYMD($year, $month, $url)
    {
        // $tday�����η��������Ķ����ޤǥ롼��
        $tday = 1;
        $mday = date('t', mktime(0, 0, 0, $month, 1, $year));
        while ($tday <= $mday) {
            if (preg_match('/\?/', $url)) {
                $url_para = $url . "&year={$year}&month={$month}&day={$tday}";
            } else {
                $url_para = $url . "?year={$year}&month={$month}&day={$tday}";
            }
            $this->link[$tday]['url']    = $url_para;
            $this->link[$tday]['title']  = "{$year}ǯ {$month}�� {$tday}�� �����Ƥ�ɽ�����ޤ���";
            $this->link[$tday]['status'] = $this->link[$tday]['title'];
            $this->link[$tday]['id']     = sprintf('%4d%02d%02d', $year, $month, $tday);
            $tday++;
        }
    }
    
    /**
     * �������ꤵ��Ƥ����󥯤����Ʋ�����ޤ���
     *
     */
    public function clear_link()
    {
        $this->link = array();
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    /**
     * �����η׻���Ԥ��ޤ���
     * �ʵ���̾�⥻�åȤ��Ƥ��ޤ��������ߤϽ��Ϥ��Ƥ��ޤ��󡣡�
     *
     * @param int $year
     * @param int $month
     */
    protected function set_holiday($year, $month)
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
            $this->holiday_name[1] = '��ö'; 
            // ���ͤ���
            if ($year < 2000) {
                $this->holiday[15] = 1;
                $this->holiday_name[15] = '���ͤ���'; 
            } else {
                $this->holiday[$day+7] = 1;
                $this->holiday_name[$day+7] = '���ͤ���'; 
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
                                $this->holiday_name[$i] = 'ǯ�ϵٲ�';
                            }
                        }
                    }
                }
            }
            break;
        case 2:
            // ����ǰ����
            $this->holiday[11] = 1;
            $this->holiday_name[11] = '����ǰ����'; 
            // ŷ��������
            if ($year > 2019) {
                $this->holiday[23] = 1;
                $this->holiday_name[23] = 'ŷ��������';
            }
            break;
        case 3:
            // ��ʬ����
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(20.8431+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holiday_name[$tmp] = '��ʬ����'; 
            }
            break;
        case 4:
            // ŷ�������� or �ߤɤ����
            $this->holiday[29] = 1;
            if ($year < 1989) {
                $this->holiday_name[29] = 'ŷ��������';
            } elseif ($year < 2017) {
                $this->holiday_name[29] = '�ߤɤ����';
            } else {
                $this->holiday_name[29] = '���¤���';
            }
            if ($year == 2019) {
                $this->holiday[30] = 1;
                $this->holiday_name[30] = '����';
            }
            if ($year == 2022) {
                $this->holiday[29] = 0;
                $this->holiday_name[29] = '���ؽж�';
            }
            break;
        case 5:
            // ��ˡ��ǰ��
            $this->holiday[3] = 1;
            $this->holiday_name[3] = '��ˡ��ǰ��';
            if ($year > 2017) {
                $this->holiday[4] = 1;
                $this->holiday_name[4] = '�ߤɤ����';
            }
            // ���ɤ����
            $this->holiday[5] = 1;
            $this->holiday_name[5] = '���ɤ����';
            if ($year == 2019) {
                $this->holiday[1] = 1;
                $this->holiday_name[1] = '��ŷ��¨����';
                $this->holiday[2] = 1;
                $this->holiday_name[2] = '����';
            }
            if ($year == 2022) {
                $this->holiday[2] = 1;
                $this->holiday_name[2] = '���ص���';
            }
            break;
        case 7:
            // ������
            // 2020ǯ��2021ǯ����������ԥå����ѥ���ԥå���������ˡ�ˤ��
            // �������ȥ��ݡ��Ĥ��������ꡢ����̵�����2022ǯ�ˤϸ������
            // �������Ϥ��Τޤޤǥ��ݡ��Ĥ�����10����ΰ������
            if ($year > 2002) {
                $this->holiday[$day+14] = 1;
                $this->holiday_name[$day+14] = '������';
            } elseif($year > 1994) {
                $this->holiday[21] = 1;
                $this->holiday_name[21] = '������';
            }
            if ($year == 2020) {
                $this->holiday[$day+14] = 0;
                $this->holiday_name[$day+14] = '';
                $this->holiday[23] = 1;
                $this->holiday_name[24] = '������';
            }
            if ($year == 2020) {
                $this->holiday[24] = 1;
                $this->holiday_name[24] = '���ݡ��Ĥ���';
            }
            
            if ($year == 2021) {
                $this->holiday[$day+14] = 0;
                $this->holiday_name[$day+14] = '';
                $this->holiday[22] = 1;
                $this->holiday_name[22] = '������';
                $this->holiday[23] = 1;
                $this->holiday_name[23] = '���ݡ��Ĥ���';
            }
            
            break;
        case 8:
            // 2020ǯ��2021ǯ����������ԥå����ѥ���ԥå���������ˡ�ˤ��
            // ��������10����8���˸��ꡢ����̵�����2022ǯ�ˤ�11�������
            if ($year > 2017) {
                $this->holiday[11] = 1;
                $this->holiday_name[11] = '������';
            }
            if ($year == 2020) {
                $this->holiday[11] = 1;
                $this->holiday_name[11] = '';
                $this->holiday[10] = 1;
                $this->holiday_name[10] = '������';
            }
            if ($year == 2021) {
                $this->holiday[11] = 1;
                $this->holiday_name[11] = '';
                $this->holiday[8] = 1;
                $this->holiday_name[8] = '������';
            }
            // ��Ҥεٶ����򥻥å�
            for ($i=5; $i<=26; $i++) {
                if (!isset($this->holiday[$i])) {
                    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                    if ( date('w',$timestamp) != 6 && date('w',$timestamp) != 0 ) {
                        if (day_off($timestamp)) {
                            $this->holiday[$i] = 1;
                            $this->holiday_name[$i] = '�ƴ��ٲ�';
                        }
                    }
                }
            }
            break;
        case 9:
            // ��Ϸ����
            if ($year < 2003) {
                $this->holiday[15] = 1;
                $this->holiday_name[15] = '��Ϸ����';
            } else {
                $this->holiday[$day+14] = 1;
                $this->holiday_name[$day+14] = '��Ϸ����';
            }
            // ��ʬ����
            if ($year > 1979 && $year < 2100) {
                $tmp = floor(23.2488+($year-1980)*0.242194-floor(($year-1980)/4));
                $this->holiday[$tmp] = 1;
                $this->holiday_name[$tmp] = '��ʬ����';
            }
            break;
        case 10;
            // �ΰ����
            // 2020ǯ��2021ǯ����������ԥå����ѥ���ԥå���������ˡ�ˤ��
            // �ΰ������7��Υ��ݡ��Ĥ����˰�ư������̵�����2022ǯ�ˤϸ������
            if ($year < 2000) {
                $this->holiday[10] = 1;
                $this->holiday_name[10] = '�ΰ����';
            } else {
                $this->holiday[$day+7] = 1;
                $this->holiday_name[$day+7] = '���ݡ��Ĥ���';
            }
            if ($year == 2019) {
                $this->holiday[22] = 1;
                $this->holiday_name[22] = '¨�������¤ε�';
            }
            
            if ($year == 2020) {
                $this->holiday[$day+7] = 0;
                $this->holiday_name[$day+7] = '';
            }
            
            if ($year == 2021) {
                $this->holiday[$day+7] = 0;
                $this->holiday_name[$day+7] = '';
            }
            
            /*
            if ($year > 2020) {
                $this->holiday[$day+7] = 0;
                $this->holiday_name[$day+7] = '';
            }
            */
            break;
        case 11:
            // ʸ������
            $this->holiday[3] = 1;
            $this->holiday_name[3] = 'ʸ������';
            
            // ��ϫ���դ���
            $this->holiday[23] = 1;
            $this->holiday_name[23] = '��ϫ���դ���';
            break;
        case 12:            
            // ŷ��������
            //if ($year > 1988) {
            if ($year < 2019) {
                $this->holiday[23] = 1;
                $this->holiday_name[23] = 'ŷ��������';
            }
            if ($year == 2014) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = 'ŷ�������� 26���ȿ��ؽж�';
            }
            if ($year == 2015) {
                $this->holiday[23] = 0;
                $this->holiday_name[23] = 'ŷ�������� 25���ȿ��ؽж�';
            }
            if ($year == 2014) {
                $this->holiday[26] = 1;
                $this->holiday_name[26] = 'ǯ���ٲ� 23���ȿ��ص���';
            }
            if ($year == 2015) {
                $this->holiday[25] = 1;
                $this->holiday_name[25] = 'ǯ���ٲ� 23���ȿ��ص���';
            }
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
                            $this->holiday_name[$i] = 'ǯ���ٲ�';
                        }
                    }
                }
            }
            break;  
        }
        
        // ��̱�ε����򥻥å�
        if ($year > 1985 && $year < 2017) {
            for ($i=1; $i<date('t', mktime(0, 0, 0, $month, 1, $year)); $i++) {
                if (isset($this->holiday[$i]) && isset($this->holiday[$i+2])) {
                    $this->holiday[$i+1] = 1;
                    $this->holiday_name[$i+1] = '��̱�ε���';
                    $i = $i + 3;
                }
            }
        }
        
        // �����ؤ������򥻥å�
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
        if ($year == 2015) {
            $this->holiday[24] = 0;
            $this->holiday_name[24] = '';
        }
    }
} // Class Calendar End

