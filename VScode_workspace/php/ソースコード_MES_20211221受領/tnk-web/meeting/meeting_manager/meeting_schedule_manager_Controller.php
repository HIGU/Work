<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ĺ�Ѳ�ĥ������塼��Ȳ�                      MVC Controller ��      //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Controller.php             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class MeetingSchedule_Controller
{
    ///// Private properties
    //private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    //private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    //private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    //private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    //private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼������ �ꥯ������ �ǡ�������
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'GanttChart');              // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        }
        if ($request->get('showMenu') == 'MyList') {
            $request->add('my_flg', 1);
        }
        ///// ��Ͽ������������� �¹Իؼ��ꥯ������
        $apend      = $request->get('Apend');               // �������塼�����Ͽ
        $delete     = $request->get('Delete');              // �������塼��μ��(���)
        $edit       = $request->get('Edit');                // �������塼����ѹ�
        ///// ��ļ���
        $roomEdit   = $request->get('roomEdit');            // ��ļ�����Ͽ���ѹ�
        $roomOmit   = $request->get('roomOmit');            // ��ļ��κ��
        $roomActive = $request->get('roomActive');          // ��ļ���ͭ����̵��(�ȥ���)
        ///// ���롼����
        $groupEdit  = $request->get('groupEdit');           // ���롼�פ���Ͽ���ѹ�
        $groupOmit  = $request->get('groupOmit');           // ���롼�פκ��
        $groupActive= $request->get('groupActive');         // ���롼�פ�ͭ����̵��(�ȥ���)
        
        ////////// MVC �� Model ���� �¹������å�����
        ///// �������塼����Խ�
        if ($apend != '') {                                 // �������塼�����Ͽ (�ɲ�)
            $this->apend($request, $model);
        } elseif ($delete != '') {                          // �������塼��μ�� (�������)
            $this->delete($request, $model);
        } elseif ($edit != '') {                            // �������塼����ѹ�
            $this->edit($request, $model);
        ///// ��ļ����Խ�
        } elseif ($roomEdit != '') {                        // ��ļ�����Ͽ���ѹ�
            $this->roomEdit($request, $model);
        } elseif ($roomOmit != '') {                        // ��ļ��κ��
            $this->roomOmit($request, $model);
        } elseif ($roomActive != '') {                      // ��ļ���ͭ����̵��(�ȥ���)
            $this->roomActive($request, $model);
        ///// ���ʼԤΥ��롼���Խ�
        } elseif ($groupEdit != '') {                        // ���롼�פ���Ͽ���ѹ�
            $this->groupEdit($request, $model);
        } elseif ($groupOmit != '') {                        // ���롼�פκ��
            $this->groupOmit($request, $model);
        } elseif ($groupActive != '') {                      // ���롼�פ�ͭ����̵��(�ȥ���)
            $this->groupActive($request, $model);
        }
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        // �����������饹��include
        require_once ('../../CalendarClass.php');   // �����������饹
        
        // ���饹�Υ��󥹥��󥹺���
        // calendar(��������, ����ʳ������դ�ɽ�����뤫�ɤ���) �η��ǻ��ꤷ�ޤ���
        // ������������0-���� ���� 6-���ˡˡ�����ʳ������դ�ɽ����0-No, 1-Yes��
        $calendar_now  = new Calendar(0, 0);
        $calendar_nex1 = new Calendar(0, 0);
        $calendar_nex2 = new Calendar(0, 0);
        $calendar_pre  = new Calendar(0, 0);

        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('meeting');
        
        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����
        $listSpan   = $request->get('listSpan');            // ����ɽ�����δ���(1����,7����,14,28...)
        
        ///// �����ե������ �ꥯ������ �ǡ�������
        $year       = $request->get('year');                // ���ͽ���ǯ����
        $month      = $request->get('month');               // ���ͽ��η��
        $day        = $request->get('day');                 // ���ͽ���������
        $str_ymd    = $year . $month . $day;                // ����ǯ����Ϣ��
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $atten_flg  = $request->get('atten_flg');           // �����Ÿ���ե饰
        
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_edit   = $request->get('cancel_edit');      // 
        
        ///// ��Ͽ���Խ� �ǡ����Υꥯ�����ȼ���
        $subject    = $request->get('subject');             // ��ķ�̾
        $str_time   = $request->get('str_time');            // ���ϻ���
        $end_time   = $request->get('end_time');            // ��λ����
        $sponsor    = $request->get('sponsor');             // ��ż�
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����) ���롼�פǤ����
        $mail       = $request->get('mail');                // �᡼������� Y/N
        $str_hour   = $request->get('str_hour');            // ���� ��
        $str_minute = $request->get('str_minute');          // ���� ʬ
        $end_hour   = $request->get('end_hour');            // ��λ ��
        $end_minute = $request->get('end_minute');          // ��λ ʬ
        ///// ��ļ��Խ���
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $room_name  = $request->get('room_name');           // ��ļ�̾
        $duplicate  = $request->get('duplicate');           // ��ļ��ν�ʣ�����å�
        $roomCopy   = $request->get('roomCopy');            // ��ļ����Խ��ǡ������ԡ�
        ///// ���롼���Խ���
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;         // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        $owner      = $request->get('owner');               // ���롼�פ�Ŀ͡���ͭ
        $groupCopy  = $request->get('groupCopy');           // ���롼�פ��Խ��ǡ������ԡ�
        ///// �Ȳ񡦰�����
        $showprint  = $request->get('showprint');           // �Ȳ�¹�
        $print      = $request->get('print');               // �����¹�
        $str_date   = $request->get('str_date');            // ��������
        $end_date   = $request->get('end_date');            // ��λ����
        
        // ���������ꥯ�����ȤΥ����å��ڤӼ���
        if ($year != '' && $month != '' && $day != '') {
            $day_now  = getdate(mktime(0, 0, 0, $month, $day, $year));
            $day_nex1 = getdate(mktime(0, 0, 0, $month+1, 1, $year));
            $day_nex2 = getdate(mktime(0, 0, 0, $month+2, 1, $year));
            $day_pre  = getdate(mktime(0, 0, 0, $month-1, 1, $year));
        } else {
            // ���������
            $day_now  = getdate();
            $day_nex1 = getdate(mktime(0, 0, 0, $day_now['mon']+1, 1, $day_now['year']));
            $day_nex2 = getdate(mktime(0, 0, 0, $day_now['mon']+2, 1, $day_now['year']));
            $day_pre  = getdate(mktime(0, 0, 0, $day_now['mon']-1, 1, $day_now['year']));
        }
        
        // ���������������դ��󥯤ˤ���
        if ($showMenu != 'Edit') {
            $url = $menu->out_self() . "?showMenu={$showMenu}&" . $model->get_htmlGETparm() . "&id={$uniq}";
        } else {
            $url = $menu->out_self() . "?showMenu=List&" . $model->get_htmlGETparm() . "&id={$uniq}";
        }
        $calendar_now-> setAllLinkYMD($day_now['year'], $day_now['mon'], $url);
        $calendar_pre-> setAllLinkYMD($day_pre['year'], $day_pre['mon'], $url);
        $calendar_nex1->setAllLinkYMD($day_nex1['year'], $day_nex1['mon'], $url);
        $calendar_nex2->setAllLinkYMD($day_nex2['year'], $day_nex2['mon'], $url);
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        $resLine  = $result->get_array();
        switch ($showMenu) {
        case 'List':                                        // �������塼�� ����ɽ�ǡ���
            $rows = $model->getViewList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ���ʼԤ�ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('List', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'MyList':                                      // �������塼�� �ޥ��ꥹ�Ȱ���ɽ
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $request->add('my_flg', 1);
            require_once ('meeting_schedule_manager_ViewGanttChartAjax.php'); // ���ߤ�Ajax�б���
            break;
        case 'Apend':                                       // �������塼������Ϥ�ɬ�פ�User�ǡ���
            // ����ɲû��μ�żԤν�������� (�ܿͤμҰ��ֹ�)
            if ($sponsor == '') if ($_SESSION['User_ID'] != '000000') $sponsor = $_SESSION['User_ID'];
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ���롼�ץޥ�������ͭ���ʥꥹ�Ȥ���� (JavaScript���Ϥ�)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // ��ļ��ޥ�������ͭ���ʥꥹ�Ȥ����
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // �������塼����ѹ� ���쥳���ɤΥǡ���
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $str_time   = $result->get_once('str_time');
            $end_time   = $result->get_once('end_time');
            $room_no    = $result->get_once('room_no');
            $sponsor    = $result->get_once('sponsor');
            $atten_num  = $result->get_once('atten_num');
            $mail       = $result->get_once('mail');
            // �Խ��Ѥ�select�ǡ�����ʬ��
            $str_hour = substr($str_time, 0, 2);
            $str_minute = substr($str_time, -2);
            $end_hour = substr($end_time, 0, 2);
            $end_minute = substr($end_time, -2);
            // ���ʼԤ�ʣ���ǡ��������
            $rowsAtten = $model->getViewAttenList($result, $serial_no);
            $resAtten  = $result->get_array();
            // ���ʼԤμҰ��ֹ�Τ����
            $atten = array();   // �����
            for ($i=0; $i<$rowsAtten; $i++) {
                $atten[$i] = $resAtten[$i][1];
            }
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ���롼�ץޥ�������ͭ���ʥꥹ�Ȥ���� (JavaScript���Ϥ�)
            $JSgroup = $model->getActiveGroupList($JSgroup_name, $JSgroup_member, $_SESSION['User_ID']);
            // ��ļ��ޥ�������ͭ���ʥꥹ�Ȥ����
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Edit', $year, $month, $day));
            break;
        case 'Room':                                        // ��ļ��� ��Ͽ ����ɽ ɽ��
            $rows = $model->getViewRoomList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'Group':                                        // ���롼�פ� ��Ͽ ����ɽ ɽ��
            if ($group_no != '') {  // �Խ�������å������group_no�����åȤ���Ƥ����
                // ���롼�׽��ʼԤ�ʣ���ǡ��������
                $rowsAtten = $model->getGroupAttenList($result, $group_no);
                $resAtten  = $result->get_array();
                // ���롼�׽��ʼԤμҰ��ֹ�Τ����
                $atten = array();   // �����
                for ($i=0; $i<$rowsAtten; $i++) {
                    $atten[$i] = $resAtten[$i][1];
                }
            }
            // ������μҰ��ֹ�Ȼ�̾��������� selected ������ޤǹԤ�
            $user_cnt = $model->getViewUserName($userID_name, $atten);
            // ���롼�ץꥹ�Ȥμ���
            $rows = $model->getViewGroupList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ���ʼԤ�ʣ���ǡ��������
            $rowsAtten = array(); $resAtten = array();      // �����
            for ($i=0; $i<$rows; $i++) {
                $rowsAtten[$i] = $model->getGroupAttenList($result, $res[$i][0]);
                $resAtten[$i]  = $result->get_array();
            }
            break;
        case 'Print':
            // ��ļ��ޥ�������ͭ���ʥꥹ�Ȥ����
            $rowsRoom = $model->getActiveRoomList($result);
            $resRoom  = $result->get_array();
            if ($showprint != '' || $print != '') {
                $rows = $model->getPrintList($result, $request);
                $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
                $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
                // ���ʼԤ�ʣ���ǡ��������
                $rowsAtten = array(); $resAtten = array();      // �����
                for ($i=0; $i<$rows; $i++) {
                    $rowsAtten[$i] = $model->getViewAttenList($result, $res[$i][0]);
                    $resAtten[$i]  = $result->get_array();
                }
            } else {
                $rows = 0;
                $rowsAtten = 0;
                $pageControl = '';
            }
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Print', $year, $month, $day));
            break;
        case 'PlanList':                                    // ��Ω�����ײ�ɽ ɽ��
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('meeting_schedule_manager_ViewPlanList.php');
            break;
        case 'ListTable':                                   // �嵭��Ajax�� ɽ��
            // $pageControl = $this->model->out_pageControll_HTML($this->menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($this->menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            $allo_parts_url = $this->menu->out_action('��������ɽ');
            require_once ('meeting_schedule_manager_ViewListTable.php');
            break;
        case 'GanttChart':                                  // �ײ�Υ���ȥ��㡼�� ɽ��
                // $rows = $this->model->getViewGanttChart($this->request, $this->result, $this->menu);
                // $res  = $this->result->get_array();
            // �ǥǡ��������Τ���嵭������˰ʲ�����ߡ��ǻ��Ѥ���(List�����ʤΤǹ�®)
            // $res  = $this->result->get_array();
            $request->add('my_flg', 0);
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
                // require_once ('assembly_schedule_show_ViewGanttChart.php');
            require_once ('meeting_schedule_manager_ViewGanttChartAjax.php'); // ���ߤ�Ajax�б���
            break;
        case 'GanttTable':                                  // �嵭��Ajax�� ɽ��
            if($listSpan == 7) {
                $range = 7;
                $request->add('range', $range);
            } elseif($listSpan == 14) {
                $range = 14;
                $request->add('range', $range);
            } elseif($listSpan == 28) {
                $range = 28;
                $request->add('range', $range);
            } else {
                $range = 0;
                $request->add('range', 0);
            }
            $year_t  = $year;
            $month_t = $month;
            $day_t   = $day;
            if ($range > 0) {
                for ($r = 1; $r <= $range; $r++) {
                    $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}-{$r}.png";
                    $rows = $model->getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, $r);
                    // ���դ�����ʤ��
                    $str_ymd = $model->computeDate($year_t, $month_t, $day_t, 1);
                    $year_t       = substr($str_ymd, 0, 4);
                    $month_t      = substr($str_ymd, 4, 2);
                    $day_t        = substr($str_ymd, 6, 2);
                }
            } else {
                $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}.png";
                $rows = $model->getViewGanttChart($request, $result, $menu, $str_ymd, $g_name, '');
            }
            $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}";
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $request->add('my_flg', 0);
            require_once ('meeting_schedule_manager_ViewGanttTable.php');
            break;
        case 'ZoomGantt':                                  // ����ȥ��㡼�ȤΤߤ��̥�����ɥ��˥���饤��ե졼��� ɽ��
            if($listSpan == 7) {
                $range = 7;
                $request->add('range', $range);
            } elseif($listSpan == 14) {
                $range = 14;
                $request->add('range', $range);
            } elseif($listSpan == 28) {
                $range = 28;
                $request->add('range', $range);
            } else {
                $range = 0;
                $request->add('range', 0);
            }
            //$rows = $model->getViewZoomGantt($request, $result, $menu);
            $g_name = "graph/MeetingScheduleManagerGanttChart-{$_SESSION['User_ID']}";
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('meeting_schedule_manager_ViewZoomGantt.php');
            // �嵭�������� _ViewZoomGanttHeader.php �� _ViewZoomGanttBody.php �򥤥�饤��ǸƽФ���
            break;
        case 'ZoomGanttAjax':                              // �嵭��Ajax�������
            $rows = $this->model->getViewZoomGantt($this->request, $this->result, $this->menu);
            // $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            break;
        }
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {
        case 'List':                                        // �������塼��ΰ��� ����
            require_once ('meeting_schedule_manager_ViewList.php');
            break;
        case 'Edit':                                        // �������塼����ѹ� ����
            require_once ('meeting_schedule_manager_ViewApend.php');    // ����
            break;
        case 'Apend':                                       // �������塼������� ����
            require_once ('meeting_schedule_manager_ViewApend.php');
            break;
        case 'Room':                                        // ��ļ��� ����ɽ ɽ��
            if ($roomCopy == 'go') {
                $focus    = 'room_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'room_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_manager_ViewRoom.php');
            break;
        case 'Group':                                       // ���롼�פ� ����ɽ ɽ��
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_manager_ViewGroup.php');
            break;
        case 'Print':                                       // �������塼��ξȲ񡦰���
            require_once ('meeting_schedule_manager_ViewPrint.php');
            break;
        
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init()
    {
        ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ����
        // showMenu�ν���
        $this->InitShowMenu();
        // showLine�ν���
        $this->InitShowLine();
        // targetLineMethod�ν���
        $this->InitLineMethod();
        // targetDate�ν���
        $this->InitTargetDate();
        // targetDateSpan�ν���
        $this->InitTargetDateSpan();
        // targetDateItem�ν���
        $this->InitTargetDateItem();
        // targetCompleteFlag�ν���
        $this->InitTargetCompleteFlag();
        // targetSeiKubun�ν���
        $this->InitTargetSeiKubun();
        // targetDept�ν���
        $this->InitTargetDept();
        // targetScale�ν���
        $this->InitTargetScale();
        // PageKeep�ν���
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'GanttChart';         // ���꤬�ʤ����ϥ���ȥ��㡼�� PlanList=�����ײ����
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajax�ξ��ϥ��å�������¸���ʤ�
        if ($showMenu != 'ListTable' && $showMenu != 'GanttTable' && $showMenu != 'ZoomGantt' && $showMenu != 'ZoomGanttAjax') {
            $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showLine�ν���
    private function InitShowLine()
    {
        $showLine = $this->request->get('showLine');
        if ($showLine == '') {
            if ($this->session->get_local('showLine') == '') {
                $showLine = '';                // ���꤬�ʤ��������ƤΥ饤�����ꤷ����Τȸ��ʤ�
            } else {
                $showLine = $this->session->get_local('showLine');
            }
        }
        if ($showLine == '0') $showLine = ''; // 0 �����Τ��̣���롣
        $this->session->add_local('showLine', $showLine);
        $this->request->add('showLine', $showLine);
    }
    
    // targetLineMethod�ν���
    private function InitLineMethod()
    {
        $LineMethod = $this->request->get('targetLineMethod');
        if ($LineMethod == '') {
            if ($this->session->get_local('targetLineMethod') == '') {
                $LineMethod = '1';              // ���꤬�ʤ�����1=���̻���Ȥ��롣2=ʣ������
            } else {
                $LineMethod = $this->session->get_local('targetLineMethod');
            }
        }
        if ($LineMethod == '1') {
            $this->session->add_local('arrayLine', array());        // �����
        } else {
            // ʣ���饤��arrayLine�ν���
            $arrayLine = $this->session->get_local('arrayLine');
            if ( ($key=array_search($this->request->get('showLine'), $arrayLine)) === false) {
                $arrayLine[] = $this->request->get('showLine');
            } else {
                // unset ($arrayLine[$key]);   // ����Ʊ���饤�󤬻��ꤵ�줿���ϥȥ��������Ǻ������������ư����ɤ���Ѥ��Ƥ��뤿�����ʤ�
            }
            $this->session->add_local('arrayLine', $arrayLine);     // ��¸
            $this->request->add('arrayLine', $arrayLine);
        }
        $this->session->add_local('targetLineMethod', $LineMethod);
        $this->request->add('targetLineMethod', $LineMethod);
    }
    
    ///// ����ǯ�����μ����������
    // targetDate�ν���
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                // $targetDate = workingDayOffset('+0');   // ���꤬�ʤ����ϱĶ���������
                $targetDate = date('Ym') . last_day();      // ���꤬�ʤ�����������
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
    }
    
    ///// ����ǯ�������ϰ� �����������
    // targetDateSpan�ν���
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = '1';   // ���꤬�ʤ����ϻ������ޤ� (�������Τ�=0)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
    }
    
    ///// ����ǯ��������λ��������������������μ����������
    // targetDateItem�ν���
    private function InitTargetDateItem()
    {
        $targetDateItem = $this->request->get('targetDateItem');
        if ($targetDateItem == '') {
            if ($this->session->get_local('targetDateItem') == '') {
                $targetDateItem = 'kanryou';   // ���꤬�ʤ���������� (kanryou, chaku, syuka)
            } else {
                $targetDateItem = $this->session->get_local('targetDateItem');
            }
        }
        $this->session->add_local('targetDateItem', $targetDateItem);
        $this->request->add('targetDateItem', $targetDateItem);
    }
    
    ///// ����ʬ��������̤����ʬ���������μ����������
    // targetCompleteFlag�ν���
    private function InitTargetCompleteFlag()
    {
        $targetCompleteFlag = $this->request->get('targetCompleteFlag');
        if ($targetCompleteFlag == '') {
            if ($this->session->get_local('targetCompleteFlag') == '') {
                $targetCompleteFlag = 'no';   // ���꤬�ʤ�����̤����ʬ (yes=complete, no=incomplete)
            } else {
                $targetCompleteFlag = $this->session->get_local('targetCompleteFlag');
            }
        }
        $this->session->add_local('targetCompleteFlag', $targetCompleteFlag);
        $this->request->add('targetCompleteFlag', $targetCompleteFlag);
    }
    
    ///// ���� ���� ��ʬ�μ����������
    // targetSeiKubun�ν���
    private function InitTargetSeiKubun()
    {
        $targetSeiKubun = $this->request->get('targetSeiKubun');
        if ($targetSeiKubun == '') {
            if ($this->session->get_local('targetSeiKubun') == '') {
                $targetSeiKubun = '0';   // ���꤬�ʤ�����0 (0=����, 1=����, 2=L�ۥ襦, 3=C����, 4=L�ԥ��ȥ�)
            } else {
                $targetSeiKubun = $this->session->get_local('targetSeiKubun');
            }
        }
        $this->session->add_local('targetSeiKubun', $targetSeiKubun);
        $this->request->add('targetSeiKubun', $targetSeiKubun);
    }
    
    ///// ���� ���� �������μ����������
    // targetDept�ν���
    private function InitTargetDept()
    {
        $targetDept = $this->request->get('targetDept');
        if ($targetDept == '') {
            if ($this->session->get_local('targetDept') == '') {
                $targetDept = '0';   // ���꤬�ʤ�����0 (0=����, C=���ץ�, L=��˥�)
            } else {
                $targetDept = $this->session->get_local('targetDept');
            }
        }
        $this->session->add_local('targetDept', $targetDept);
        $this->request->add('targetDept', $targetDept);
    }
    
    ///// �����६��ȥ��㡼�Ȥ���Ψ����
    // targetScale�ν���
    private function InitTargetScale()
    {
        $targetScale = $this->request->get('targetScale');
        if ($targetScale == '') {
            if ($this->session->get_local('targetScale') == '') {
                $targetScale = '1.0';   // ���꤬�ʤ�����1.0��ɽ��
            } else {
                $targetScale = $this->session->get_local('targetScale');
            }
        }
        if ($targetScale < 0.3) $targetScale = '0.3';
        if ($targetScale > 1.7) $targetScale = '1.7';
        $this->session->add_local('targetScale', $targetScale);
        $this->request->add('targetScale', $targetScale);
    }
    
    ///// �ײ��ֹ�ǰ�����������ɽ��Ȳ񤷤���������ͤ�����å�
    // page_keep���������material_plan_no �ڤӥڡ�������ν���
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            // ����å������ײ��ֹ�ιԤ˥ޡ�������
            if ($this->session->get('material_plan_no') != '') {
                $this->request->add('material_plan_no', $this->session->get('material_plan_no'));
            }
            // �ڡ��������� (�ƽФ������Υڡ������᤹)
            if ($this->session->get_local('viewPage') != '') {
                $this->request->add('CTM_viewPage', $this->session->get_local('viewPage'));
            }
            if ($this->session->get_local('pageRec') != '') {
                $this->request->add('CTM_pageRec', $this->session->get_local('pageRec'));
            }
        }
    }
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ///// ��Ͽ(�ɲ�) ����
    protected function apend($request, $model)
    {
        $response = $model->add($request);
        if ($response) {
            $request->add('subject', '');                           // ��Ͽ�Ǥ����Τ����ϥե�����ɤ�ä�
            $request->add('str_time', '');
            $request->add('end_time', '');
            $request->add('room_no', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'List');                      // ��Ͽ�Ǥ����Τǰ������̤ˤ��롣
        } else {
            $str_time = $request->get('str_time');
            $end_time = $request->get('end_time');
            $request->add('str_hour', substr($str_time, 0, 2));     // ��Ͽ�Ǥ��ʤ��ä��Τ�select�ǡ���������
            $request->add('str_minute', substr($str_time, -2));
            $request->add('end_hour', substr($end_time, 0, 2));
            $request->add('end_minute', substr($end_time, -2));
        }
    }
    
    ///// ���(�������) ����
    protected function delete($request, $model)
    {
        // $serial_no  = $request->get('serial_no');                   // ���ꥢ���ֹ�
        // $subject    = $request->get('subject');                     // ��ķ�̾
        // $response = $model->delete($serial_no, $subject);
        $response = $model->delete($request);                       // ����󥻥�Υ᡼���б���
        if ($response) {
            $request->add('showMenu', 'List');                      // ������褿�Τǰ������̤ˤ��롣
        } else {
            $request->add('showMenu', 'Edit');                      // �������ʤ��ä��Τ��Խ����̤�����
        }
    }
    
    ///// �Խ�����  ����
    protected function edit($request, $model)
    {
        if ($model->edit($request)) {
            $request->add('subject', '');                           // �ѹ��Ǥ����Τ����ϥե�����ɤ�ä�
            $request->add('str_time', '');
            $request->add('end_time', '');
            $request->add('room_no', '');
            $request->add('sponsor', '');
            $request->add('mail', '');
            $request->add_array('atten', '1', '');
            $request->add('showMenu', 'List');                      // ��Ͽ�Ǥ����Τǰ������̤ˤ��롣
        } else {
            $request->add('showMenu', 'Edit');                      // ��Ͽ����ʤ��ä��Τ��Խ����̤�����
        }
    }
    
    ///// ��ļ����Խ� ����
    protected function roomEdit($request, $model)
    {
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $room_name  = $request->get('room_name');           // ��ļ�̾
        $duplicate  = $request->get('duplicate');           // ��ļ��ν�ʣ�����å�
        if ($model->room_edit($room_no, $room_name, $duplicate)) {
            // ��Ͽ�Ǥ����Τ�room_no, room_name��<input>�ǡ�����ä�
            $request->add('room_no', '');
            $request->add('room_name', '');
            $request->add('duplicate', '');
        }
    }
    
    ///// ��ļ��κ�� ����
    protected function roomOmit($request, $model)
    {
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $room_name  = $request->get('room_name');           // ��ļ�̾
        $response = $model->room_omit($room_no, $room_name);
        $request->add('room_no', '');                       // ������ϥ��ԡ���ɬ�פʤ�
        $request->add('room_name', '');
        $request->add('duplicate', '');
    }
    
    ///// ��ļ���ͭ����̵�� ����
    protected function roomActive($request, $model)
    {
        $room_no    = $request->get('room_no');             // ��ļ��ֹ�
        $room_name  = $request->get('room_name');           // ��ļ�̾
        if ($model->room_activeSwitch($room_no, $room_name)) {
            $request->add('room_no', '');
            $request->add('room_name', '');
            $request->add('duplicate', '');
        }
    }
    
    ///// ���ʼԥ��롼�פ��Խ� ����
    protected function groupEdit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        $atten      = $request->get('atten');               // ���ʼ�(attendance) (����) ���롼�פǤ����
        $owner      = $request->get('owner');               // ���롼�פ�Ŀ͡���ͭ
        if ($model->group_edit($group_no, $group_name, $atten, $owner)) {
            // ��Ͽ�Ǥ����Τ�group_no, group_name��<input>�ǡ�����ä�
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
    ///// ���ʼԥ��롼�פκ�� ����
    protected function groupOmit($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        $response = $model->group_omit($group_no, $group_name);
        $request->add('group_no', '');                      // ������ϥ��ԡ���ɬ�פʤ�
        $request->add('group_no2', '');
        $request->add('group_name', '');
        $request->del('atten');
    }
    
    ///// ���ʼԥ��롼�פ�ͭ����̵�� ����
    protected function groupActive($request, $model)
    {
        $group_no2  = $request->get('group_no2');           // ���롼���ֹ�
        $group_no   = $group_no2;                           // TEST
        $group_name = $request->get('group_name');          // ���롼��̾
        if ($model->group_activeSwitch($group_no, $group_name)) {
            $request->add('group_no', '');
            $request->add('group_no2', '');
            $request->add('group_name', '');
            $request->del('atten');
        }
    }
    
} // class AssemblyScheduleShow_Controller End

?>
