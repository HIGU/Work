<?php
//////////////////////////////////////////////////////////////////////////////
// ������夲�κ�����(������)�� �Ȳ�                 MVC Controller ��      //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_Controller.php                  //
// 2006/02/20 InitTargetItemNo()�Υ᥽�åɤ���̵꤬�����ν����򥳥���  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PartsMaterialShow_Controller
{
    ///// Private properties
    private $menu;                              // TNK ���ѥ�˥塼���饹�Υ��󥹥���
    private $request;                           // HTTP Controller���Υꥯ������ ���󥹥���
    private $result;                            // HTTP Controller���Υꥶ���   ���󥹥���
    private $session;                           // HTTP Controller���Υ��å���� ���󥹥���
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    
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
        $this->model = new PartsMaterialShow_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('partsMShow');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            require_once ('parts_material_show_ViewCondForm.php');
            break;
        case 'ListTable':                                   // Ajax�� Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            require_once ('parts_material_show_ViewListTable.php');
            break;
        case 'WaitMsg':                                     // Ajax�� ������Ǥ������Ԥ���������
            require_once ('parts_material_show_ViewWaitMsg.php');
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
        // showDiv�ν���
        $this->InitShowDiv();
        // targetDateStr�ν���
        $this->InitTargetDateStr();
        // targetDateEnd�ν���
        $this->InitTargetDateEnd();
        // targetItemNo�ν���
        $this->InitTargetItemNo();
        // targetSalesSegment�ν���
        $this->InitTargetSalesSegment();
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
        if ($showMenu != 'ListTable' || $showMenu != 'WaitMsg') {
            // ����ϾȲ�Τߤ�ñ��ե�����ʤΤǥ��å����ϻȤ�ʤ�
            // $this->session->add_local('showMenu', $showMenu);
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    // showDiv�ν���
    private function InitShowDiv()
    {
        $showDiv = $this->request->get('showDiv');
        if ($showDiv == '') {
            if ($this->session->get_local('showDiv') == '') {
                $showDiv = '';                // ���꤬�ʤ��������Ƥ���ꤷ����Τȸ��ʤ�
            } else {
                $showDiv = $this->session->get_local('showDiv');
            }
        }
        if ($showDiv == '0') $showDiv = ''; // 0 �����Τ��̣���롣
        $this->session->add_local('showDiv', $showDiv);
        $this->request->add('showDiv', $showDiv);
    }
    
    ///// ����ǯ�����μ����������
    // targetDateStr�ν���
    private function InitTargetDateStr()
    {
        $targetDateStr = $this->request->get('targetDateStr');
        if ($targetDateStr == '') {
            if ($this->session->get_local('targetDateStr') == '') {
                $targetDateStr = workingDayOffset(-1);      // ���꤬�ʤ��������Ķ���
            } else {
                $targetDateStr = $this->session->get_local('targetDateStr');
            }
        }
        $this->session->add_local('targetDateStr', $targetDateStr);
        $this->request->add('targetDateStr', $targetDateStr);
    }
    
    ///// ����ǯ�����μ����������
    // targetDateEnd�ν���
    private function InitTargetDateEnd()
    {
        $targetDateEnd = $this->request->get('targetDateEnd');
        if ($targetDateEnd == '') {
            if ($this->session->get_local('targetDateEnd') == '') {
                $targetDateEnd = workingDayOffset(-1);      // ���꤬�ʤ��������Ķ���
            } else {
                $targetDateEnd = $this->session->get_local('targetDateEnd');
            }
        }
        $this->session->add_local('targetDateEnd', $targetDateEnd);
        $this->request->add('targetDateEnd', $targetDateEnd);
    }
    
    ///// �������������ֹ�μ����������
    // targetItemNo�ν���
    private function InitTargetItemNo()
    {
        $targetItemNo = $this->request->get('targetItemNo');
        /*****
        if ($targetItemNo == '') {
            if ($this->session->get_local('targetItemNo') == '') {
                $targetItemNo = '';   // ���꤬�ʤ����������о�
            } else {
                $targetItemNo = $this->session->get_local('targetItemNo');
            }
        }
        *****/
        $this->session->add_local('targetItemNo', $targetItemNo);
        $this->request->add('targetItemNo', $targetItemNo);
    }
    
    ///// ����ʬ 1=����(����), 2=���ʹ��(5��9), 5=����(��ư), 6=����(ľǼNKT), 7=����(���), 8=����(����), 9=����(����)
    // targetSalesSegment�ν��� (���ߤ����ʤΤߤ��оݤȤ���)
    private function InitTargetSalesSegment()
    {
        $targetSalesSegment = $this->request->get('targetSalesSegment');
        if ($targetSalesSegment == '') {
            if ($this->session->get_local('targetSalesSegment') == '') {
                $targetSalesSegment = '2';   // ���꤬�ʤ��������ʹ��
            } else {
                $targetSalesSegment = $this->session->get_local('targetSalesSegment');
            }
        }
        $this->session->add_local('targetSalesSegment', $targetSalesSegment);
        $this->request->add('targetSalesSegment', $targetSalesSegment);
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
    
} // class PartsMaterialShow_Controller End

?>
