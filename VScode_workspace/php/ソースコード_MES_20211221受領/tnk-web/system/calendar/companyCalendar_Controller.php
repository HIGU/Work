<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ҥδ��ܥ������� ���ƥʥ�                     MVC Controller ��  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/20 Created   companyCalendar_Controller.php                      //
// 2006/07/07 showMenu �� TimeCopy �ɲ� display()�᥽�å�                   //
// 2006/07/11 Controller��Execute()�᥽�åɤ��ɲä�Action��showMenu�����β� //
// 2006/11/29 ���������ν����˥塼��$targetCalendar = 'SetTime' ���ѹ�  //
// 2007/02/06 ����ͤ���ǯɽ��������ɽ�����ѹ� InitTargetDateY()�ν���      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// require_once ('../../tnk_func.php');        // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class CompanyCalendar_Controller
{
    ///// Private properties
    private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    private $uniq;                              // �֥饦�����Υ���å����к���
    private $error = 0;                         // ���顼���������ϥե饰
    private $errorMsg = '';                     // ���顼��å�����
    
    private $calendar = array();                // �����������֥������Ȥ�����
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu)
    {
        ///// MenuHeader ���饹�Υ��󥹥��󥹤� properties ����Ͽ
        if (is_object($menu)) {
            $this->menu = $menu;
        } else {
            exit();
        }
        //////////// �ꥯ�����ȤΥ��󥹥��󥹤���Ͽ
        $this->request = new Request();
        
        //////////// �ꥶ��ȤΥ��󥹥��󥹤���Ͽ
        $this->result = new Result();
        
        //////////// ���å����Υ��󥹥��󥹤���Ͽ
        $this->session = new Session();
        
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init();
        
        //////////// ���������Υ��󥹥��󥹤���Ͽ
        for ($i=0; $i<12; $i++) {
            $this->calendar[$i] = new CalendarTNK();
        }
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new CompanyCalendar_Model($this->request, $this->menu);
        
        //////////// �֥饦�����Υ���å����к���
        $this->uniq = $this->menu->set_useNotCache('companyCalendar');
    }
    
    ///// MVC �� Model�� �¹ԥ��å��ν���
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'Change':                                      // ��Ҥε������Ķ����ȥ�������
            $this->model->changeHoliday($this->request, $this->result, $this->menu);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Calendar');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸ ���ե���
            $this->model->commentSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'EditComment');
            break;
        case 'Comment':                                     // �����ȤξȲ��Խ��ѥǡ�������
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'EditComment');
            break;
        case 'TimeList':                                    // �ܺ��Խ��ѥꥹ��(������)����
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'List');
            break;
        case 'bdDetailSave':                                // �ܺ��Խ����Ķ���������������
            $this->model->changeHoliday($this->request, $this->result, $this->menu);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'bdCommentSave':                               // �ܺ��Խ����Ķ����������Υ��������Խ�
            $this->model->commentSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'TimeSave':                                    // �����Խ��ǡ�������¸ ���ե���
            $this->model->timeSave($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'TimeCopy':                                    // �о�����ľ��Υǡ����򥳥ԡ�����ɽ��
            $this->model->getTimeDetail($this->request, $this->result, 2);  // 2=���ԡ�(����ľ��Υǡ�����ʬ�����դ˥��ԡ�)
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'TimeEdit');
            break;
        case 'Format':                                      // �оݴ��Σ�ǯ�֤���������
            $this->model->deleteCalendar($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Calendar');
            break;
        ////////// (���ߤϻ��Ѥ��Ƥ��ʤ�)submit��
        case 'CommentEdit':                                 // �������Խ�����
            $this->model->commentEdit($this->request, $this->result, $this->menu);
                // �������ե������Ƨ����ˤ���ɽ��
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'CondForm');
            break;
        default:
            // showMenu�ν���
            $this->InitShowMenu($this->request);
        }
    }
    
    ///// MVC View���ν���
    public function display()
    {
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->request, $this->model, $this->calendar, $this->result, $this->uniq);
            break;
        case 'Calendar':                                    // Ajax�� ��������ɽ��
            $this->ViewCalendarExecute($this->menu, $this->request, $this->model, $this->calendar, $this->uniq);
            break;
        case 'EditComment':                                 // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            $this->ViewCommentExecute($this->menu, $this->request, $this->result, $this->uniq);
            break;
        case 'List':                                        // Ajax�� �ܺ��Խ��ѥꥹ��(������)ɽ��
                // $this->model->getViewListTable($this->request, $this->result);
                // �嵭�η�̤� $rows = $this->result->get('rows'), $res = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu, $this->uniq);
            $this->ViewListExecute($this->uniq);
            break;
        case 'TimeEdit':                                    // �̥�����ɥ����о����ξܺ��Խ���Ԥ�
            $this->model->getTimeDetail($this->request, $this->result, 1);  // 1=��ʬ�Υǡ����Τ߼���
            $this->ViewTimeEditExecute($this->menu, $this->request, $this->result, $this->model, $this->uniq);
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init()
    {
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ����
        // PageKeep�ν���
        $this->InitPageKeep($this->request, $this->session);
        // targetDateY�ν���
        $this->InitTargetDateY($this->request, $this->session);
        // targetDateStr�ν���
        $this->InitTargetDateStr($this->request);
        // targetDateEnd�ν���
        $this->InitTargetDateEnd($this->request);
        // targetCalendar�ν���
        $this->InitTargetCalendar($this->request, $this->session);
        // targetFormat�ν���
        $this->InitTargetFormat($this->request);
        // ���顼����
        if ($this->error) {
            $_SESSION['s_sysmsg'] = $this->errorMsg;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ������Ȳ񤷤���������ͤ�����å�
    // page_keep���������rec_no �ڤӥڡ�������ν���
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // �����Graph���١��������ڡ��������List����ɬ��
            // ����å������Ԥ˥ޡ�������
            if ($session->get_local('rec_no') != '') {
                $request->add('rec_no', $session->get_local('rec_no'));
            }
            // �ڡ��������� (�ƽФ������Υڡ������᤹)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        }
    }
    
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu($request)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // ���꤬�ʤ�����Condition Form (�������)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// ���ǯ�μ����������
    // targetDateY�ν���
    private function InitTargetDateY($request, $session)
    {
        $targetDateY = $request->get('targetDateY');
        if ($targetDateY == '') {
            if ($session->get_local('targetDateY') == '') {
                $targetDateY = date('Y');       // ���꤬�ʤ�������ǯ
                if (date('m') < 4) {            // ����Ĵ��(4��3�����)
                    $targetDateY--;
                }
            } else {
                $targetDateY = $session->get_local('targetDateY');
            }
        }
        $session->add_local('targetDateY', $targetDateY);
        $request->add('targetDateY', $targetDateY);
    }
    
    ///// ����ǯ�����μ����������
    // targetDateStr�ν���
    private function InitTargetDateStr($request)
    {
        if ($request->get('targetDateY') == '') return;
        // if ($request->get('targetDateStr') != '') return;    // �����С�¦�Ƿ׻��򤹤뤿��ꥯ�����Ȥ�̵��
        $request->add('targetDateStr', $request->get('targetDateY') . '04');
    }
    
    ///// ��λǯ�����μ����������
    // targetDateEnd�ν���
    private function InitTargetDateEnd($request)
    {
        if ($request->get('targetDateY') == '') return;
        // if ($request->get('targetDateEnd') != '') return;    // �����С�¦�Ƿ׻��򤹤뤿��ꥯ�����Ȥ�̵��
        $request->add('targetDateEnd', ($request->get('targetDateY') + 1) . '03');
    }
    
    ///// ���������Υ��ơ����������������
    // targetCalendar�ν���
    private function InitTargetCalendar($request, $session)
    {
        $targetCalendar = $request->get('targetCalendar');
        if ($targetCalendar == '') {
            if ($session->get_local('targetCalendar') == '') {
                // $targetCalendar = 'BDSwitch';                       // ���꤬�ʤ����ϱĶ����ȵ���������
                $targetCalendar = 'SetTime';                        // ���꤬�ʤ����Ͼܺ��Խ��⡼�ɤ�
            } else {
                $targetCalendar = $session->get_local('targetCalendar');
            }
        }
        $session->add_local('targetCalendar', $targetCalendar);
        $request->add('targetCalendar', $targetCalendar);
    }
    
    ///// �����������������μ������¹�
    // targetFormat�ν���
    private function InitTargetFormat($request)
    {
        if ($request->get('targetFormat') == 'Execute') {
            $request->add('Action', 'Format');
        }
        return;
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $request, $model, $calendar, $result, $uniq)
    {
        require_once ('companyCalendar_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewCalendarExecute($menu, $request, $model, $calendar, $uniq)
    {
        $model->showCalendar($request, $calendar, $menu, $uniq);
        require_once ('companyCalendar_ViewCalendar.php');
        return;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// �������Ķ����Υ������Խ��ե�����
    private function ViewCommentExecute($menu, $request, $result, $uniq)
    {
        require_once ('companyCalendar_ViewEditComment.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// �ĶȻ��֤ȵٷƻ��֤��Խ��ѥꥹ��ɽ��
    private function ViewListExecute($uniq)
    {
        require_once ('companyCalendar_ViewList.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// �ĶȻ��֤ȵٷƻ��֤��Խ��� Windowɽ��
    private function ViewTimeEditExecute($menu, $request, $result, $model, $uniq)
    {
        require_once ('companyCalendar_ViewTimeEdit.php');
        return true;
    }
    
} // class CompanyCalendar_Controller End

?>
