<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ��¤����ڤ��δ���ξȲ�                       MVC Controller ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_Controller.php                          //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ActSummary_Controller
{
    ///// Private properties
    private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
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
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new ActSummary_Model();
    }
    
    ///// MVC Control�� �¹ԥ��å����ؤν���
    public function execute()
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init($this->request, $this->session);
        ///// �ꥯ�����ȤΥ�����������
        /***** ����ϥ���������̵��
        switch ($this->request->get('Action')) {
        case 'ClearSort':                                   // �����Ȥβ��
            $this->session->add_local('targetSortItem', '');
            $this->request->add('targetSortItem', '');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->setComment($this->request, $this->result, $this->session);
            break;
        case 'EditFactor':                                  // �װ��ޥ��������Խ�
            $this->model->editFactor($this->request, $this->result, $this->session);
            break;
        case 'DeleteFactor':                                // �װ��ޥ������κ������̵����
            $this->model->deleteFactor($this->request, $this->result, $this->session);
            break;
        case 'ActiveFactor':                                // �װ��ޥ�������ͭ����
            $this->model->activeFactor($this->request, $this->result, $this->session);
            break;
        }
        *****/
        ///// SQL �� WHERE��Υ��å�
        if ($this->request->get('showMenu') != 'CondForm') {
            $this->model->setWhere($this->session);
        }
        // $this->model->setOrder($this->session);
        // $this->model->setOffset($this->session);
        // $this->model->setLimit($this->session);
        // $this->model->setSQL($this->session);
    }
    
    ///// MVC Control�� View���ؤν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('assyTimeComp');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'List':                                        // Ajax�� �ꥹ��ɽ��
        case 'ListWin':                                     // Ajax�� �̥�����ɥ���Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->session, $this->menu);
            $this->ViewListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('act_summary_ViewEditComment.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init($request, $session)
    {
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ����
        // PageKeep�ν���
        $this->InitPageKeep($request, $session);
        // showMenu�ν���
        $this->InitShowMenu($request);
        // targetDateYM�ν���
        $this->InitTargetDateYM($request, $session);
        // targetAct_id�ν���
        $this->InitTargetAct_id($request, $session);
        
        //////////// �������ƤΥ��顼�������
        $this->errorCheck($request, $session);
    }
    
    ////////// ���顼�����������ƥ��顼�λ���Ŭ�ڤʥ쥹�ݥ󥹤��֤�
    protected function errorCheck($request, $session)
    {
        if ($this->error != 0) {
            // $request->add('showMenu', 'CondForm');
            $session->add('s_sysmsg', $this->errorMsg);
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'CondForm');  // ���꤬�ʤ�����Condition Form (�������)
        }
    }
    
    ///// �о�ǯ��μ����������
    // targetDateYM�ν���
    private function InitTargetDateYM($request, $session)
    {
        $targetDateYM = $request->get('targetDateYM');
        if ($targetDateYM == '') {
            if ($session->get_local('targetDateYM') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
                // $targetDateYM = date('Ym');         // ���꤬�ʤ���������
            } else {
                $targetDateYM = $session->get_local('targetDateYM');
            }
        }
        $session->add_local('targetDateYM', $targetDateYM);
        $request->add('targetDateYM', $targetDateYM);
        if (!is_numeric($targetDateYM)) {
            $this->error++;
            $this->errorMsg = '�о�ǯ��Ͽ��������Ϥ��Ʋ�������';
        }
        if (strlen($targetDateYM) != 6) {
            $this->error++;
            $this->errorMsg = '�о�ǯ��ϣ���Ǥ���';
        }
    }
    
    ///// ���祳���ɤμ����������
    // targetAct_id�ν���
    private function InitTargetAct_id($request, $session)
    {
        $targetAct_id = $request->get('targetAct_id');
        if ($targetAct_id == '') {
            if ($session->get_local('targetAct_id') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                $targetAct_id = $session->get_local('targetAct_id');
            }
        }
        $session->add_local('targetAct_id', $targetAct_id);
        $request->add('targetAct_id', $targetAct_id);
        if (!is_numeric($targetAct_id)) {
            $this->error++;
            $this->errorMsg = '���祳���ɤϿ��������Ϥ��Ʋ�������';
        }
        if (strlen($targetAct_id) != 3) {
            $this->error++;
            $this->errorMsg = '���祳���ɤϣ���Ǥ���';
        }
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('act_summary_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('act_summary_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('act_summary_ViewListWin.php');
        }
        return true;
    }
    
} // class ActSummary_Controller End

?>
