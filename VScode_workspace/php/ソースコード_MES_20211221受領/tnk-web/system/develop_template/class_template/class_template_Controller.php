<?php
//////////////////////////////////////////////////////////////////////////////
// ���饹�ο���                                          MVC Controller ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/09/21 Created   class_template_Controller.php                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/****************************************************************************
*           MVC��Controller�� ���饹���  base class ���쥯�饹             *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ClassTemplate_Controller
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
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new AcceptanceInspectionAnalyze_Model($this->request);
    }
    
    ///// MVC �� Model�� �¹ԥ��å��ν���
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'ListLeadTime':                                // ���������Υꥹ������
            $this->model->getListLeadTime($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'List');
            break;
        case 'ListInspection':                              // ô������μ�����������ꥹ������
            $this->model->listInspection($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'List');
            break;
        case 'GraphLeadTime':                               // ���������Υ��������
            $this->model->graphLeadTime($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Graph');
            break;
        case 'GraphInspection':                             // ô������μ�������������������
            $this->model->graphInspection($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'Graph');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->commentSave($this->request);
        default:
            // showMenu�ν���
            $this->InitShowMenu($this->request);
        }
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('accInsAna');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax�� �ꥹ��ɽ��
        case 'ListWin':                                     // �̥�����ɥ���Listɽ��
            $this->ViewListExecute($this->menu, $this->request, $this->result, $this->model, $uniq);
            break;
        case 'Graph':                                       // Ajax�� �����ɽ��
        case 'GraphWin':                                    // �̥�����ɥ��ǥ����ɽ��
            $this->ViewGraphExecute($this->menu, $this->request, $this->result, $this->model, $uniq);
            break;
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('acceptance_inspection_analyze_ViewEditComment.php');
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
        // Action�ν���
        $this->InitAction($this->request);
        
        // targetDateStr�ν���
        $this->InitTargetDateStr($this->request, $this->session);
        // targetDateEnd�ν���
        $this->InitTargetDateEnd($this->request, $this->session);
        
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
    
    ///// �¹Խ����ѤΥǡ��������� �� ����
    // Action�ν���
    private function InitAction($request)
    {
        $Action = $request->get('Action');
        if ($request->get('Action') == '') {
            $request->add('Action', 'StartForm');           // ���꤬�ʤ����ϸ��¥ޥ������ΰ���
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
    
    ///// ����ǯ�����μ����������
    // targetDateStr�ν���
    private function InitTargetDateStr($request, $session)
    {
        $targetDateStr = $request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($session->get_local('targetDateStr') == '') {
                $targetDateStr = ''; // workingDayOffset(-1);      // ���꤬�ʤ��������Ķ���
            } else {
                $targetDateStr = $session->get_local('targetDateStr');
            }
        }
        $session->add_local('targetDateStr', $targetDateStr);
        $request->add('targetDateStr', $targetDateStr);
        return;
        if (!is_numeric($targetDateStr)) {
            $this->error++;
            $this->errorMsg = '�������դϿ��������Ϥ��Ʋ�������';
        }
        if (strlen($targetDateStr) != 8) {
            $this->error++;
            $this->errorMsg = '�������դϣ���Ǥ���';
        }
    }
    
    ///// ��λǯ�����μ����������
    // targetDateEnd�ν���
    private function InitTargetDateEnd($request, $session)
    {
        $targetDateEnd = $request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($session->get_local('targetDateEnd') == '') {
                $targetDateEnd = ''; // workingDayOffset(-1);      // ���꤬�ʤ��������Ķ���
            } else {
                $targetDateEnd = $session->get_local('targetDateEnd');
            }
        }
        $session->add_local('targetDateEnd', $targetDateEnd);
        $request->add('targetDateEnd', $targetDateEnd);
        return;
        if (!is_numeric($targetDateEnd)) {
            $this->error++;
            $this->errorMsg = '��λ���դϿ��������Ϥ��Ʋ�������';
        }
        if (strlen($targetDateEnd) != 8) {
            $this->error++;
            $this->errorMsg = '��λ���դϣ���Ǥ���';
        }
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('acceptance_inspection_analyze_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $request, $result, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('acceptance_inspection_analyze_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('acceptance_inspection_analyze_ViewListWin.php');
        }
        return true;
    }
    
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewGraphExecute($menu, $request, $result, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'Graph':
            require_once ('acceptance_inspection_analyze_ViewGraphAjax.php');
            break;
        case 'GraphWin':
        default:
            require_once ('acceptance_inspection_analyze_ViewGraphWin.php');
        }
        return true;
    }
    
} // class AcceptanceInspectionAnalyze_Controller End

?>
