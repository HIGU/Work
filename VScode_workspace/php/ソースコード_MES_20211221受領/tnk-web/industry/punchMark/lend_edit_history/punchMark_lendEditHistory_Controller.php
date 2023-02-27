<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �߽���Ģ ���� �����˥塼           MVC Controller ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/04 Created   punchMark_lendEditHistory_Controller.php            //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PunchMarkLendEditHistory_Controller
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
        $this->model = new PunchMarkLendEditHistory_Model();
    }
    
    ///// MVC Control�� �¹ԥ��å����ؤν���
    public function execute()
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init($this->request, $this->session);
        ///// �ꥯ�����ȤΥ�����������
        switch ($this->request->get('Action')) {
        case 'Search':                                      // �����¹�
            $this->model->setWhere($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'Sort':                                        // �����ȼ¹�
            $this->model->setWhere($this->session);
            $this->model->setOrder($this->session);
            $this->model->setSQL($this->session);
            break;
        case 'SortClear':                                   // �����Ȥβ��
            $this->session->add_local('targetSortItem', '');
            $this->request->add('targetSortItem', '');
            break;
        }
        // $this->model->setWhere($this->session);
        // $this->model->setOrder($this->session);
        // $this->model->setOffset($this->session);
        // $this->model->setLimit($this->session);
        // $this->model->setSQL($this->session);
    }
    
    ///// MVC Control�� View���ؤν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('punchMarkEditHistory');
        
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
        
        // �оݹ������Ƥν���
        $this->InitTargetHistory($request, $session);
        
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
            $request->add('showMenu', 'List');  // �ڡ������椬ɬ�פʤΤǶ���Ū��List�ˤ���
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
    
    ///// �оݹ������Ƥμ����������
    private function InitTargetHistory($request, $session)
    {
        if ($request->get('targetHistory') == '') {
            if ($session->get_local('targetHistory') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetHistory', '');
            }
        } else {
            // ���������ɻ�
            // $request->add('targetHistory', mb_convert_kana($request->get('targetHistory'), 'a'));
            $session->add_local('targetHistory', $request->get('targetHistory'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('punchMark_lendEditHistory_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('punchMark_lendEditHistory_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('punchMark_lendEditHistory_ViewListWin.php');
        }
        return true;
    }
    
} // class PunchMarkLendEditHistory_Controller End

?>
