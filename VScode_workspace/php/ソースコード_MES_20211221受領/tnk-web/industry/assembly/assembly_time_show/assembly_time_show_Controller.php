<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω����Ͽ�����ȼ��ӹ�������� �Ȳ�               MVC Controller ��      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/01 Created   assembly_time_show_Controller.php                   //
// 2006/03/03 ¾�Υ�˥塼��������ܤξ���������ɲ� InitShowMenu()����   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyTimeShow_Controller
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
        
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init();
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new AssemblyTimeShow_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('assyTimeShow');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            require_once ('assembly_time_show_ViewCondForm.php');
            break;
        case 'ListTable':                                   // Ajax�� Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            require_once ('assembly_time_show_ViewListTable.php');
            break;
        case 'ProcessTable':                                // Ajax�� ��������ɽ��
            require_once ('assembly_time_show_ViewProcessTable.php');
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
        // showMenu�ν���
        $this->InitShowMenu();
        if ($this->request->get('showMenu') == 'ListTable') {
            // targetPlanNo�ν���
            $this->InitTargetPlanNo();
        }
        if ($this->request->get('showMenu') == 'ProcessTable') {
            // targetAssyNo�ν���
            $this->InitTargetAssyNo();
            // targetRegNo�ν���
            $this->InitTargetRegNo();
        }
        // PageKeep�ν���
        $this->InitPageKeep();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            if ($this->session->get_local('showMenu') == '') {
                $showMenu = 'CondForm';         // ���꤬�ʤ�����Condition Form (�������)
            } else {
                $showMenu = $this->session->get_local('showMenu');
            }
        }
        // Ajax�ξ��ϥ��å�������¸���ʤ�
        if ($showMenu != 'ListTable' || $showMenu != 'ProcessTable') {
            // ����ϾȲ�Τߤ�ñ��ե�����ʤΤǥ��å����ϻȤ�ʤ�
            // $this->session->add_local('showMenu', $showMenu);
        }
        // ¾�Υ�˥塼��������ܤξ�������
        if ($showMenu == 'CondForm') {
            if ($this->request->get('targetPlanNo')) $this->menu->set_retGET('page_keep', 'on');
            $this->session->add('material_plan_no', $this->request->get('targetPlanNo'));
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    ///// �ײ��ֹ�μ����������
    // targetPlanNo�ν���
    private function InitTargetPlanNo()
    {
        $targetPlanNo = $this->request->get('targetPlanNo');
        // ��������å�
        if (strlen($targetPlanNo) != 8) {
            $this->error = 1;
            $this->errorMsg = '�ײ��ֹ�η���ϣ���Ǥ���';
            return false;
        }
        if (!is_numeric(substr($targetPlanNo, 2, 6))) {
            $this->error = 1;
            $this->errorMsg = '�ײ��ֹ�β�����Ͽ��������Ϥ��Ʋ�������';
            return false;
        }
        // $this->session->add_local('targetPlanNo', $targetPlanNo);
        // $this->request->add('targetPlanNo', $targetPlanNo);
        return true;
    }
    
    ///// �����ֹ�μ����������
    // targetAssyNo�ν���
    private function InitTargetAssyNo()
    {
        $targetAssyNo = $this->request->get('targetAssyNo');
        // ��������å�
        if (strlen($targetAssyNo) != 9) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�η���ϣ���Ǥ���';
            return false;
        }
        if (!is_numeric(substr($targetAssyNo, 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '�ײ��ֹ�Σ����ܤ��飷��ޤǤϿ����Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ��Ͽ�ֹ�μ����������
    // targetRegNo�ν���
    private function InitTargetRegNo()
    {
        $targetRegNo = $this->request->get('targetRegNo');
        // ��������å�
        if (strlen($targetRegNo) <= 7) {
            $this->error = 1;
            $this->errorMsg = '��Ͽ�ֹ�η���ϣ���ʲ��Ǥ���';
            return false;
        }
        if (!is_numeric($targetRegNo)) {
            $this->error = 1;
            $this->errorMsg = '��Ͽ�ֹ�Ͽ����Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ������Ȳ񤷤���������ͤ�����å�
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
    
} // class AssemblyTimeShow_Controller End

?>
