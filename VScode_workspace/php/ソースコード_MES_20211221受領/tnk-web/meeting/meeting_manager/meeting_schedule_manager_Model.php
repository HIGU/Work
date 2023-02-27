<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ĺ�Ѳ�ĥ������塼��Ȳ�         MVC Model ��                        //
// Copyright (C) 2010 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Model.php                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../ComTableMntClass.php');    // TNK ������ �ơ��֥����&�ڡ�������Class


/*****************************************************************************************
* ����Ĺ�Ѳ�ĥ������塼��Ȳ� MVC��Model���� base class ���쥯�饹�����                *
*****************************************************************************************/
class MeetingSchedule_Model extends ComTableMnt
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $whereNoLine;                       // ���� SQL��WHERE��(Line�������)
    private $GraphName;
                                                // GanttChart�Υե�����̾
    
    ///// public properties
    public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// �ץ�ѥƥ����ν����
        $this->GraphName = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}.png";
        // �ʲ��Υꥯ�����Ȥ�controller�����˼������Ƥ��뤿����ξ�礬���롣
        $year       = $request->get('year');
        $month      = $request->get('month');
        $day        = $request->get('day');
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        $listSpan   = $request->get('listSpan');
        $room_no    = $request->get('room_no');
        $str_date   = $request->get('str_date');
        $end_date   = $request->get('end_date');
        if ($str_date == '') {
            $str_date = $year . $month . $day;
        }
        if ($end_date == '') {
            $end_date = $year . $month . $day;
        }
        if ($request->get('showMenu') == 'MyList') {
            $request->add('my_flg', 1);
        }
        switch ($request->get('showMenu')) {
        case 'GanttChart':
            // ����WHERE�������
            if ($request->get('CTM_pageRec') > 100) {
                $request->add('CTM_pageRec', 100);
                $_SESSION['s_sysmsg'] = '����ȥ��㡼�ȤǤϣ������ԤޤǤǤ���\n\n�������Ԥ�Ĵ�����ޤ�����';
            }
            // 100��ɽ���Ǹ��경
            $request->add('CTM_pageRec', 100);
            $this->where = "WHERE str_time>='{$year}-{$month}-{$day} 00:00:00' AND str_time<=(timestamp '{$year}-{$month}-{$day} 23:59:59' + interval '{$listSpan} day')";
            $sql_sum = "
                SELECT count(*) FROM meeting_schedule_header {$this->where}
            ";
            break;
        case 'PlanList':
        case 'Room':
            $this->where = '';
            $sql_sum = "
                SELECT count(*) FROM meeting_room_master {$this->where}
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
            $request->add('my_flg', 1);
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
        parent::__construct($sql_sum, $request, 'meeting_schedule_manager.log');
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// �ޥ͡����㡼��Ͽ��ȴ�Ф�
    public function getViewManager(&$result)
    {
        $query = "
            SELECT id,                            -- 00
                   name                           -- 01
            FROM
                common_authority AS c
            LEFT OUTER JOIN
                user_detailes AS u on c.id =u.uid
            WHERE
                division = 33
            ORDER BY id
        ";
        // ����ë��Ĺ011061
        $res_m = array();
        if ( ($rows_m=$this->execute_List($query, $res_m)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��Ͽ������ޤ���';
        }
        $result->add_array($res_m);
        return $rows_m;
    }
    
    ///// MyList��
    public function getViewMyList(&$result, $u_id, $str_date, $end_date)
    {
        $this->where = "'{$u_id}', timestamp '{$str_date}', timestamp '{$end_date}'";
        $query = "
            SELECT serial_no                            -- 00
                ,subject                                -- 01
                ,to_char(str_time, 'YYYY-MM-DD HH24:MI')  -- 02
                ,to_char(end_time, 'YYYY-MM-DD HH24:MI')  -- 03
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
        // ����ë��Ĺ011061
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
    
    ///// Get��    ����ȥ��㡼�ȤΥ饤�󥿥��ȥ�ɽ���ѥǡ�������
    public function getLineTitle($request)
    {
        if ($request->get('targetLineMethod') == '1') {
            // ���̻���
            if ($request->get('showLine')) $showLine = $request->get('showLine').' '; else $showLine = '���� ';
        } else {
            // ʣ������
            $arrayLine = $request->get('arrayLine');
            $showLine = '';
            for ($i=0; $i<count($arrayLine); $i++) {
                $showLine .= $arrayLine[$i] . ' ';
            }
        }
        return $showLine;
    }
    
    ///// Get��    ����ȥ��㡼��ɽ���ѥǡ�������
    public function getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, $map)
    {
        $this->GraphName = $g_name;
        //$graph = new GanttGraph(990, -1, 'auto');   // -1=��ư, 0=�Ǥ������̵���ä�
        $graph = new GanttGraph(990, -1);   // 'auto'��������3/8���ʤ������顼���ä�
        $graph->SetShadow();
        $graph->img->SetMargin(15, 17, 10, 15);     // default�� 25��10 ���ѹ�
        // ������ѥ����ȥ����
        //if ($request->get('targetSeiKubun') == '1')
        //    $sei_kubun = 'ɸ����';
        //elseif ($request->get('targetSeiKubun') == '3')
        //    $sei_kubun = '������';
        //else
        //    $sei_kubun = '����';
        // Add title and subtitle
        $graph->title->Set(mb_convert_encoding("����Ĺ�������塼��", 'UTF-8'));
        $graph->title->SetFont(FF_GOTHIC, FS_NORMAL, 14);
            // $graph->subtitle->SetFont(FF_GOTHIC, FS_NORMAL, 10);
            // $graph->subtitle->Set(mb_convert_encoding("����饤��{$showLine} ���ʶ�ʬ��{$sei_kubun}", 'UTF-8'));
        // Show day, week and month scale
        $graph->ShowHeaders(GANTT_HDAY | GANTT_HHOUR);
        //$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);
        //$graph->ShowHeaders(GANTT_HMIN | GANTT_HHOUR | GANTT_HDAY);
        // $graph->ShowHeaders(GANTT_HDAY | GANTT_HMONTH);
        // 1.5 line spacing to make more room
        $graph->SetVMarginFactor(1.0);      // ���μ��Ӥ�ɽ����ȼ�� 2.5��1.0 ��
        // Setup some nonstandard colors
        $graph->SetMarginColor('lightgreen@0.8');
        $graph->SetBox(true, 'yellow:0.6', 2);
        $graph->SetFrame(true, 'darkgreen', 4);
        $graph->scale->divider->SetColor('yellow:0.6');
        $graph->scale->dividerh->SetColor('yellow:0.6');
        // ����̾������
        //$graph->scale->tableTitle->Set(mb_convert_encoding("\n�ײ�No. ����No. ����\n�������ʡ���̾", 'UTF-8'));
        //$graph->scale->tableTitle->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        //$graph->scale->SetTableTitleBackground('darkgreen@0.6');
        //$graph->scale->tableTitle->Show(true);
        $item1 = mb_convert_encoding("̾������", 'UTF-8');
        $item2 = mb_convert_encoding("\n�и�Ψ", 'UTF-8');
        $graph->scale->actinfo->SetColTitles(array($item1));
        $graph->scale->actinfo->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        $graph->scale->actinfo->vgrid->SetColor('gray');
        $graph->scale->actinfo->SetBackgroundColor('darkgreen@0.6');
        $graph->scale->actinfo->SetColor('darkgray');
        
        // ��󥸤���ꤹ�� �������Ƚ�λ��������
        //$year  = substr($request->get('targetDate'), 0, 4);
        //$month = substr($request->get('targetDate'), 4, 2);
        //$lastDay = last_day($year, $month);
        //$graph->scale->SetRange($year.$month.'01', $year.$month.$lastDay);
        //$year       = $request->get('year');
        //$month      = $request->get('month');
        //$day        = $request->get('day');
        $year       = substr($str_ymd, 0, 4);
        $month      = substr($str_ymd, 4, 2);
        $day        = substr($str_ymd, 6, 2);
        //$year       = '2010';
        //$month      = '03';
        //$day        = '08';
        
        $str_range = $year . '-' . $month . '-' . $day . ' 07:00';
        $end_range = $year . '-' . $month . '-' . $day . ' 21:00';
        $str_date  = $year . '-' . $month . '-' . $day . ' 00:00';
        $end_date  = $year . '-' . $month . '-' . $day . ' 23:59';
        
        $graph->scale->SetRange($str_range, $end_range);
        // Make the WEEK scale
        $graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
        $graph->scale->week->SetFont(FF_FONT1);
        // Make the hour scale
        $graph->scale->hour->SetIntervall('1:00');
        $graph->scale->hour->SetStyle(HOURSTYLE_HM24);
        $graph->scale->hour->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the day scale
        $graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE5);
        $graph->scale->day->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the MINUTES scale
        //$graph->scale->minute->SetStyle(MINUTESTYLE_MM);
        //$graph->scale->minute->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Make the hour scale
        //$graph->scale->hour->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Instead of week number show the date for the first day in the week
        // on the week scale
        //$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY2);
        // Make the week scale font smaller than the default
        //$graph->scale->week->SetFont(FF_GOTHIC, FS_NORMAL, 12);
        // Use the short name of the month together with a 2 digit year
        // on the month scale
        //$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
        //$graph->scale->month->SetFont(FF_GOTHIC, FS_NORMAL, 14);
        //$graph->scale->month->SetFontColor('white');
        //$graph->scale->month->SetBackgroundColor('blue');
        
        // ɽ�����դκǾ��ͤ����
        $graph->scale->AdjustStartEndDay();     // �ޥ˥奢���̵���᥽�åɡ��ץ�ѥƥ������
        $viewStartDate = date('Ymd', $graph->scale->iStartDate);
        
        if ($request->get('my_flg') != 1) {
            $rows_m = $this->getViewManager($result);
            if ($rows_m <= 0) return $rows_m;   // �ǡ�����̵�����Chart�Ϻ��ʤ�
            $res_m = $result->get_array();
        } else {
            $rows_m      = 1;
            $res_m[0][0] = $_SESSION['User_ID'];
            $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$res_m[0][0]}'";
            if (getUniResult($query, $name) <= 0) {
                $rows_m = 0;
                return $rows_m;   // �ǡ�����̵�����Chart�Ϻ��ʤ�
            }
            $res_m[0][1] = $name;
        }
        for ($i=0; $i<$rows_m; $i++) {
            $num = 0;   // ����հ��֤�����(�����ɲäˤ��)
            $u_id   = $res_m[$i][0];
            $u_name = $res_m[$i][1];
        
            // �оݥǡ��������(PlanList�Υǡ�����Ȥ�)
            $rows = $this->getViewMyList($result, $u_id, $str_date, $end_date);
            //if ($rows <= 0) return $rows;   // �ǡ�����̵�����Chart�Ϻ��ʤ�
            // GanttChart�κ���
            $res = $result->get_array();
        
            // CSIM�ѥǡ��������
            $targ = array();
            $alts = array();
            if ($rows <= 0) {
                $plan_no  = '';    // �ײ��ֹ�
                $assy_no  = '';    // �����ֹ�
                $assy_name= '';    // ����̾
                $plan_zan = '';    // �ײ�Ŀ�
                $syuka    = '';    // ������
                $chaku    = '';    // ��������
                $kanryou  = '';    // ��λ����
                $bikou    = '';    // ����
                $plan_pcs = '';    // �ײ��
                $cut_pcs  = '';    // ���ڤ��
                $end_pcs  = '';   // ������
                $ritu     = '';   // �и�Ψ
                $kousu    = '';   // ��Ͽ����
                
                $item = mb_convert_encoding("{$u_name}", 'UTF-8');
                $strDate = '19700101';
                $endDate = '19700101';
                $activity[$num] = new GanttBar($i, array($item), $strDate, $endDate);
                $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                $activity[$num]->caption->SetColor('blue');
                $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                $activity[$num]->SetFillColor('red');
                $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
                if ($u_id == $_SESSION['User_ID']) {
                    $activity[$num]->title->SetColor('red');
                }
                // CSIM �ǡ�������
                //$this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
                $graph->Add($activity[$num]);

                $num++;     // ���μ���ɽ���Τ��ᥤ�󥯥����
                $num++;
            } else {
                for ($r=0; $r<$rows; $r++) {
                    $plan_no  = $res[$r][0];    // �ײ��ֹ�
                    $assy_no  = $res[$r][1];    // �����ֹ�
                    $assy_name= $res[$r][2];    // ����̾
                    $plan_zan = $res[$r][3];    // �ײ�Ŀ�
                    $syuka    = $res[$r][4];    // ������
                    
                    //$chaku    = $res[$r][5];    // �����
                    //$kanryou  = $res[$r][6];    // ��λ��
                    $chaku    = $res[$r][2];    // ��������
                    $kanryou  = $res[$r][3];    // ��λ����
                    
                    $chaku_h  = substr($chaku, 11, 2) + 1 + $r;
                    $kanryou_h= substr($kanryou, 11, 2) + 2 + $r;
                    
                    $chaku2   = substr($chaku, 0, 11) . $chaku_h . ':' . substr($chaku, 14, 2);    // ��������Ĵ��
                    $kanryou2 = substr($kanryou, 0, 11) . $kanryou_h . ':' . substr($kanryou, 14, 2);    // ��λ����Ĵ��
                    
                    $bikou    = $res[$r][7];    // ����
                    $plan_pcs = $res[$r][8];    // �ײ��
                    $cut_pcs  = $res[$r][9];    // ���ڤ��
                    $end_pcs  = $res[$r][10];   // ������
                    $ritu     = $res[$r][11];   // �и�Ψ
                    $kousu    = $res[$r][12];   // ��Ͽ����
                    $assy_name = mb_convert_kana($assy_name, 'k');
                    
                    //$manager_name = $u_name;
                    $item = mb_convert_encoding("{$u_name}", 'UTF-8');
                    
                    //$item = mb_convert_encoding("{$plan_no}", 'UTF-8');
                                            // ($row, $title, $startdate, $enddate)
                    // ��λ�����㤦���ϥ���ץ�����ɽ��
                    //if (substr($kanryou, 4, 2) != $month && substr($chaku, 4, 2) != $month) {
                        if ($num > 0 ) {
                            $activity[$num] = new GanttBar($i, '', $chaku, $kanryou);
                        } else {
                            $activity[$num] = new GanttBar($i, array($item), $chaku, $kanryou);
                        }
                        //$activity[$num] = new GanttBar($num, array($item), $viewStartDate, $viewStartDate);
                        //$activity[$num]->caption->Set(mb_convert_encoding("{$chaku}��{$kanryou}", 'UTF-8'));
                        $activity[$num]->caption->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                        $activity[$num]->caption->SetColor('blue');
                        $activity[$num]->SetPattern(BAND_RDIAG, 'white');
                        if ($res[$r][4] == '��ĥ') {
                            $activity[$num]->SetFillColor('red');
                        } elseif ($res[$r][4] == '����') {
                            $activity[$num]->SetFillColor('green');
                        } else {
                            $activity[$num]->SetFillColor('blue');
                        }
                    //} else {
                    //    $activity[$num] = new GanttBar($num, array($item), $chaku, $kanryou);
                        //$activity[$num] = new GanttBar($num, array($item, $ritu_kousu), $chaku, $kanryou);
                        // Yellow diagonal line pattern on a red background
                    //    $activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                    //    $activity[$num]->SetFillColor('blue');
                    //    $activity[$num]->SetShadow(true, 'black');
                    //}
                    // $activity[$num]->title->Align('right', 'center');  // �������Ȳ��̤������SetAlign��Ʊ��
                    $activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 10);
                    if ($u_id == $_SESSION['User_ID']) {
                        $activity[$num]->title->SetColor('red');
                    }
                    // CSIM �ǡ�������
                    $this->setActivityCSIM($activity[$num], $request, $menu, $res[$r]);
                    $graph->Add($activity[$num]);
                    
                    $num++;     // ���μ���ɽ���Τ��ᥤ�󥯥����
                    //$activity[$num]->title->SetFont(FF_GOTHIC, FS_NORMAL, 12);
                    //$activity[$num]->SetPattern(BAND_RDIAG, 'yellow');
                    //$activity[$num]->SetFillColor('teal');
                    //$activity[$num]->SetShadow(true, 'black');
                    // CSIM �ǡ�������
                    // $this->setActivityCSIMreal($activity[$num], $request, $menu, $res[$r]);
                    //$this->setActivityCSIMrealWin($activity[$num], $request, $menu, $res[$r]);
                    //$graph->Add($activity[$num]);
                    
                    $num++;
                }
                // ��Ҥε��������������
                //$j = 0; $vline = array();   // �����
                //for ($i=(-5); $i<37; $i++) {
                //    $timestamp = mktime(0, 0, 0, $month, $i, $year);
                //    if (date('w',$timestamp) == 0) continue;    // ������
                //    if (date('w',$timestamp) == 6) continue;    // ������
                //    if (day_off($timestamp)) {  // ��Ҥε٤ߤ�����å�
                //        $vline[$j] = new GanttVLine(date('Ymd', $timestamp));
                //        $vline[$j]->SetDayOffset(0.5);
                //        $graph->Add($vline[$j]);
                //        $j++;
                //    }
                //}
                //return $rows;
            }
        }
        $graph->Stroke($this->GraphName);
        $map_name = "myimagemap" . $map;
        $graph->StrokeCSIM($this->GraphName, $map_name, 0);
        chmod($this->GraphName, 0666);     // file������rw�⡼�ɤˤ���
        $this->graph = $graph;              // View�ǻ��Ѥ��뤿��graph���֥������Ȥ���¸
        return $rows_m;
    }
    public function computeDate($year, $month, $day, $addDays) 
    {
        $baseSec = mktime(0, 0, 0, $month, $day, $year);//��������äǼ���
        $addSec = $addDays * 86400;//�����ߣ������ÿ�
        $targetSec = $baseSec + $addSec;
        return date("Ymd", $targetSec);
    }
    
    ///// Get��    GanttChart�Υե�����̾���֤�
    public function getGraphName()
    {
        return $this->GraphName;
    }
    
    ///// Get��    GanttChart��إå������ȥܥǥ�����ʬ�䤷�ƥꥶ��Ȥ˥ե�����̾���֤�
    public function getViewZoomGantt($request, $result, $menu)
    {
        // ����եե�����κǽ���������������ƹ������뤫���ꤹ��
        // clearstatcache();   // �ե����륹�ơ������Υ���å���򥯥ꥢ
        if ( (mktime() - filemtime($this->GraphName)) > 60) {   // ����եե����뤬�������줿�Τ�60�����ʤ�
            $this->getViewGanttChart($request, $result, $menu); // ��������
        }
        // ���ꥸ�ʥ�Υ���եե����뤫�饳�ԡ����������[gd-png:  fatal libpng error: IDAT: CRC error]����� 2006/07/26 ADD
        $header_height  = 87;                   // �����������θ��Ф��ι⤵
        $scale = $request->get('targetScale');  // ����������������Ψ����
        $tempGraphName = $this->GraphName . session_id() . '.png';
        while (!copy($this->GraphName, $tempGraphName)) {
            sleep(2);   // ����եե�����򥳥ԡ��Ǥ��ʤ���У��ä��餷�ƥȥ饤
        }
        $src_id = imagecreatefrompng($tempGraphName);
        $src_x  = imagesx($src_id);
        $src_y  = imagesy($src_id);
        $src_header_y   = $header_height;
        $src_body_y     = $src_y - $header_height;
        $dst_header_x   = $src_x * $scale;
        $dst_header_y   = $header_height * $scale;
        $dst_body_x     = $src_x * $scale;
        $dst_body_y     = ($src_y - $header_height) * $scale;
        $dst_header_id  = imagecreatetruecolor($dst_header_x, $dst_header_y);
        $dst_body_id    = imagecreatetruecolor($dst_body_x, $dst_body_y);
        imagecopyresampled($dst_header_id, $src_id, 0, 0, 0, 0, $dst_header_x, $dst_header_y, $src_x, $src_header_y);
        imagecopyresampled($dst_body_id, $src_id, 0, 0, 0, $header_height, $dst_body_x, $dst_body_y, $src_x, $src_body_y);
        $dst_header_file = ('zoom/MeetingScheduleManagerZoomGanttHeader-' . $_SESSION['User_ID'] . '.png');
        $dst_body_file   = ('zoom/MeetingScheduleManagerZoomGanttBody-' . $_SESSION['User_ID'] . '.png');
        ImagePng ($dst_header_id, $dst_header_file);
        ImagePng ($dst_body_id, $dst_body_file);
        chmod($dst_header_file, 0666);      // file������rw�⡼�ɤˤ���
        chmod($dst_body_file, 0666);        // file������rw�⡼�ɤˤ���
        ImageDestroy ($dst_header_id);
        ImageDestroy ($dst_body_id);
        ImageDestroy ($src_id);
        if (file_exists($tempGraphName)) {  // 2007/08/21 ����ɲ�
            unlink($tempGraphName); // 2006/07/26 ADD
        }
        $result->add('zoomGanttHeader', $dst_header_file);
        $result->add('zoomGanttbody', $dst_body_file);
        return;
    }
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
        $mail       = $request->get('mail');                // �᡼������� Y/N
        // ǯ�����Υ����å�  ���ߤ� Main Controller�ǽ���ͤ����ꤷ�Ƥ���Τ�ɬ�פʤ��������Τޤ޻Ĥ���
        if ($year == '') {
            // ���������դ�����
            $year = date('Y'); $month = date('m'); $day = date('d');
        }
        // ���ϡ���λ ���֤ν�ʣ�����å�
        if ($this->duplicateCheck("{$year}-{$month}-{$day} {$str_time}:00", "{$year}-{$month}-{$day} {$end_time}:00", $room_no)) {
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
            FROM
                meeting_schedule_header AS meet
            LEFT OUTER JOIN
                meeting_room_master USING(room_no)
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
        if ( ($rows=$this->execute_List($query, $res)) < 1 ) {
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
    
    ///// Set��  Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ�����
    protected function setActivityCSIM($activity, $request, $menu, $res)
    {
        $subject   = str_ireplace('<br>', '��', $res[1]);    // ���̾
        //$subject   = $res[1];    // ���̾
        $str_time  = $res[2];    // ���ϻ���
        $end_time  = $res[3];    // ��λ����
        $room_name = $res[4];    // ��ľ��
        $sponsor   = $res[6];    // ��ż�
        $atten_num = $res[7];    // ���ü�
        $kanryou  = $res[6];    // ��λ��
        $bikou    = $res[7];    // ����
        $plan_pcs = $res[8];    // �ײ��
        $cut_pcs  = $res[9];    // ���ڤ��
        $end_pcs  = $res[10];   // ������
        $ritu     = $res[11];   // �и�Ψ
        $targ1 = "JavaScript:alert('���̾��{$subject}\\n\\n���ϻ��֡�{$str_time}\\n\\n��λ���֡�{$end_time}\\n\\n��ľ�ꡧ{$room_name}\\n\\n��żԡ�{$sponsor}\\n\\n���üԡ�{$atten_num}̾')";
        $alts1 = "���̾��{$subject}�����ϻ��֡�{$str_time}����λ���֡�{$end_time}����ľ�ꡧ{$room_name}";
        //$targ2 = "{$menu->out_action('��������ɽ')}?plan_no=".urlencode($plan_no)."&material=1&id={$menu->out_useNotCache()}";
        //$alts2 = '���ηײ��ֹ�ΰ������ʹ���ɽ��ɽ�����ޤ���';
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
            //$targ2 = mb_convert_encoding($targ2, 'UTF-8', 'EUC-JP');
            //$alts2 = mb_convert_encoding($alts2, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        //$activity->title->SetCSIMTarget($targ2, $alts2);
        //if ($request->get('material_plan_no') == $plan_no) {
        //    $activity->title->SetColor('red');  // �ޡ�������
        //}
    }
    
    ///// Set��  ���ӥ��㡼���� Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ�����
    protected function setActivityCSIMreal($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // �ײ��ֹ�
        $assy_name= $res[2];    // ����̾
        $targ1 = "{$menu->out_action('���ӹ����Ȳ�')}?showMenu=CondForm&targetPlanNo=" . urlencode($plan_no);
        $alts1 = "����̾��{$assy_name}������Ͽ�����ȼ��ӹ�����Ȳ񤷤ޤ���";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
    
    ///// Set��  ���ӥ��㡼���� Activity �� CSIM���� showMenu�����Ƥ�ʸ�������ɤ����� Window��
    protected function setActivityCSIMrealWin($activity, $request, $menu, $res)
    {
        $plan_no  = $res[0];    // �ײ��ֹ�
        $assy_name= $res[2];    // ����̾
        $targ1 = "javascript:AssemblyScheduleShow.win_open('{$menu->out_action('���ӹ����Ȳ�')}?targetPlanNo=" . urlencode($plan_no) . "&noMenu=yes', 900, 600)";
        $alts1 = "����̾��{$assy_name}������Ͽ�����ȼ��ӹ�����Ȳ񤷤ޤ���";
        if ($request->get('showMenu') == 'GanttTable') {
            $targ1 = mb_convert_encoding($targ1, 'UTF-8', 'EUC-JP');
            $alts1 = mb_convert_encoding($alts1, 'UTF-8', 'EUC-JP');
        }
        $activity->SetCSIMTarget($targ1, $alts1);
        $activity->title->SetCSIMTarget($targ1, $alts1);
    }
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
        // ���ϻ��֤ν�ʣ�����å�
        $chk_sql1 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$str_timestamp}'
            AND end_time > '{$str_timestamp}'
            AND room_no = {$room_no}
            {$deselect}
            limit 1
        ";
        // ��λ���֤ν�ʣ�����å�
        $chk_sql2 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time < '{$end_timestamp}'
            AND end_time > '{$end_timestamp}'
            AND room_no = {$room_no}
            {$deselect}
            limit 1
        ";
        // ���Τν�ʣ�����å�
        $chk_sql3 = "
            SELECT subject FROM meeting_schedule_header
            WHERE str_time >= '{$str_timestamp}'
            AND end_time <= '{$end_timestamp}'
            AND room_no = {$room_no}
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
            (subject, str_time, end_time, room_no, sponsor, atten_num, mail, last_date, last_host)
            VALUES
            ('$subject', '{$year}-{$month}-{$day} {$str_time}', '{$year}-{$month}-{$day} {$end_time}', $room_no, '$sponsor', $atten_num, $mail, '$last_date', '$last_host')
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
            last_date='{$last_date}', last_host='{$last_host}'
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
    
} // Class AssemblyScheduleShow_Model End

?>
