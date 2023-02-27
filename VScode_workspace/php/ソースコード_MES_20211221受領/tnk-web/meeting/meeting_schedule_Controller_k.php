<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ �ǹ礻(���)�������塼��ɽ�ξȲ񡦥��ƥʥ�                  //
//                                                       MVC Controller ��  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Controller.php                     //
// 2005/11/21 ���ʼԤΥ��롼�׻�����ɲ�                                    //
// 2005/11/29 ����������̤�裲����Ȳ�����δ������ $day �� 1 ���ѹ� //
// 2006/05/09 ��ʬ�Υ������塼��Τ�ɽ��(�ޥ��ꥹ��)��ǽ���ɲ�              //
// 2007/05/10 ��ĺ�����˥���󥻥�Υ᡼�������Τ���delete()�᥽�åɤ��ѹ�//
// 2008/09/01 ���ʼ��ޤꤿ����ɽ���ΰ�$atten_flg�μ����Ϥ����ɲ�       ��ë //
// 2009/12/17 �Ȳ񡦰����Ѳ���(Print)�ƥ���                            ��ë //
// 2010/03/11 ����Ĺ�ѥ������塼����������ݤ˥ƥ����ѹ�             ��ë //
// 2015/06/19 �ײ�ͭ��ξȲ���ɲ�                                     ��ë //
// 2019/03/15 �䲹�嵡��Ư���������Ѽ֡��Ժ߼ԤΥ�˥塼���ɲ�         ��ë //
// 2019/03/19 �ѹ����ν�����ϳ�줬���ä��Τǽ���                       ��ë //
// 2021/06/10 ����������ư�Ѥ�ǯ������Ϥ��Ϥ���ʤ��ä��ΤǺ��     ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class MeetingSchedule_Controller
{
    ///// Private properties
    //private $showMenu;                  // ��˥塼����
    //private $listSpan;                  // ����ɽ�����δ���(1����,7����,14,28...)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼������ �ꥯ������ �ǡ�������
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'List');              // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
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
        ///// ���Ѽ���
        $carEdit  = $request->get('carEdit');               // ���Ѽ֤���Ͽ���ѹ�
        $carOmit  = $request->get('carOmit');               // ���Ѽ֤κ��
        $carActive= $request->get('carActive');             // ���Ѽ֤�ͭ����̵��(�ȥ���)
        ///// �ײ�ͭ����
        $hdelete    = $request->get('hdel');                // �ײ�ͭ����Ͽ�κ��
        
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
        ///// ���Ѽ֤Υ��롼���Խ�
        } elseif ($carEdit != '') {                          // ���Ѽ֤���Ͽ���ѹ�
            $this->carEdit($request, $model);
        } elseif ($carOmit != '') {                          // ���Ѽ֤κ��
            $this->carOmit($request, $model);
        } elseif ($carActive != '') {                        // ���Ѽ֤�ͭ����̵��(�ȥ���)
            $this->carActive($request, $model);
        ///// �ײ�ͭ��κ��
        } elseif ($hdelete != '') {                          // �ײ�ͭ��κ��
            $this->holydayDelete($request, $model);
        }
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        // �����������饹��include
        require_once ('../CalendarClass.php');   // �����������饹
        
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
        ///// ���Ѽ��Խ���
        $car_no     = $request->get('car_no');              // ���Ѽ��ֹ�
        $car_name   = $request->get('car_name');            // ���Ѽ�̾
        $car_dup    = $request->get('car_dup');             // ���Ѽ֤ν�ʣ�����å�
        $carCopy    = $request->get('carCopy');             // ���Ѽ֤��Խ��ǡ������ԡ�
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
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
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
            $rows = $model->getViewMyList($result);
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
            $rowsCar  = $model->getActiveCarList($result);
            $resCar   = $result->get_array();
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Apend', $year, $month, $day));
            break;
        case 'Edit':                                        // �������塼����ѹ� ���쥳���ɤΥǡ���
            $rows       = $model->getViewEdit($serial_no, $result);
            $subject    = $result->get_once('subject');
            $str_time   = $result->get_once('str_time');
            $end_time   = $result->get_once('end_time');
            $room_no    = $result->get_once('room_no');
            $car_no     = $result->get_once('car_no');
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
            // ���Ѽ֥ޥ�������ͭ���ʥꥹ�Ȥ����
            $rowsCar  = $model->getActiveCarList($result);
            $resCar   = $result->get_array();
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
        case 'Car':                                        // ���Ѽ֤� ��Ͽ ����ɽ ɽ��
            $rows = $model->getViewCarList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
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
        case 'Holyday':                                        // �ײ�ͭ��
            $rows = $model->getViewHolyday($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Holyday', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Absence':                                        // �Ժ�ͽ��
            $rows = $model->getViewAbsence($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Absence', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        case 'Over':                                        // �Ķ�ͽ��
            $rows = $model->getViewOver($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            // ɽ��(����ץ����)������
            $menu->set_caption($model->get_caption('Over', $year, $month, $day));
            // �ǡ����ʤ��λ��Υ�å���������
            $noDataMessage = $model->get_noDataMessage($year, $month, $day);
            break;
        }
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {
        case 'List':                                        // �������塼��ΰ��� ����
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_room.php');
            } else {
                require_once ('meeting_schedule_ViewList_k.php');
            }
            break;
        case 'Edit':                                        // �������塼����ѹ� ����
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_apend.php');        // ����
            } else {
                require_once ('meeting_schedule_ViewApend.php');    // ����
            }
            break;
        case 'Apend':                                       // �������塼������� ����
            if( $request->get('only') == 'yes' ) {
                require_once ('meeting_schedule_apend.php');
            } else {
                require_once ('meeting_schedule_ViewApend.php');
            }
            break;
        case 'Room':                                        // ��ļ��� ����ɽ ɽ��
            if ($roomCopy == 'go') {
                $focus    = 'room_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'room_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewRoom.php');
            break;
        case 'Car':                                         // ���Ѽ֤� ����ɽ ɽ��
            if ($carCopy == 'go') {
                $focus    = 'car_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'car_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewCar.php');
            break;
        case 'Group':                                       // ���롼�פ� ����ɽ ɽ��
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#e6e6e6;'";
            } else {
                $focus    = 'group_no';
                $readonly = '';
            }
            require_once ('meeting_schedule_ViewGroup.php');
            break;
        case 'Print':                                       // �������塼��ξȲ񡦰���
            require_once ('meeting_schedule_ViewPrint.php');
            break;
        case 'Holyday':                                     // �ײ�ͭ��ΰ��� ����
            require_once ('meeting_schedule_ViewHolyday.php');
            break;
        case 'Absence':                                     // �ײ�ͭ��ΰ��� ����
            require_once ('meeting_schedule_ViewAbsence.php');
            break;
        case 'Over':                                     // �Ķ�ͽ��ΰ��� ����
            require_once ('meeting_schedule_ViewOver_k.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('meeting_schedule_ViewList_k.php');
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
            $request->add('car_no', '');
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
            $request->add('car_no', '');
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
    
    ///// ���Ѽ֤��Խ� ����
    protected function carEdit($request, $model)
    {
        $car_no    = $request->get('car_no');             // ���Ѽ��ֹ�
        $car_name  = $request->get('car_name');           // ���Ѽ�̾
        $car_dup   = $request->get('car_dup');            // ���Ѽ֤ν�ʣ�����å�
        if ($model->car_edit($car_no, $car_name, $car_dup)) {
            // ��Ͽ�Ǥ����Τ�car_no, car_name��<input>�ǡ�����ä�
            $request->add('car_no', '');
            $request->add('car_name', '');
            $request->add('car_dup', '');
        }
    }
    
    ///// ���Ѽ֤κ�� ����
    protected function carOmit($request, $model)
    {
        $car_no    = $request->get('car_no');             // ���Ѽ��ֹ�
        $car_name  = $request->get('car_name');           // ���Ѽ�̾
        $response = $model->car_omit($car_no, $car_name);
        $request->add('car_no', '');                       // ������ϥ��ԡ���ɬ�פʤ�
        $request->add('car_name', '');
        $request->add('car_dup', '');
    }
    
    ///// ���Ѽ֤�ͭ����̵�� ����
    protected function carActive($request, $model)
    {
        $car_no    = $request->get('car_no');             // ���Ѽ��ֹ�
        $car_name  = $request->get('car_name');           // ���Ѽ�̾
        if ($model->car_activeSwitch($car_no, $car_name)) {
            $request->add('car_no', '');
            $request->add('car_name', '');
            $request->add('car_dup', '');
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
    
    ///// �ײ�ͭ��κ��(�������) ����
    protected function holydayDelete($request, $model)
    {
        $response = $model->hdelete($request);                       // ����󥻥�Υ᡼���б���
        if ($response) {
            $request->add('showMenu', 'Holyday');                    // �ɤä��ˤ���Ʊ�����̤�
        } else {
            $request->add('showMenu', 'Holyday');                    // �ɤä��ˤ���Ʊ�����̤�
        }
    }
} // End off Class MeetingSchedule_Controller

?>
