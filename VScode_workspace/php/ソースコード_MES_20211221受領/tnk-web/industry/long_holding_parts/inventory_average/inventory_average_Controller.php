<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� ���ʼ�η�ʿ�ѽи˿�����ͭ������Ȳ�      MVC Controller �� //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/23 Created   inventory_average_Controller.php                    //
// 2007/06/08 �ǥե���ȤΥ����ȥ����ƥ��߸˶�ۤ��ѹ�                    //
// 2007/06/11 public�᥽�å�Execute()���ɲ�                                 //
// 2007/06/14 �װ��ޥ��������Խ��������ȡ��װ�����Ͽ�Խ� ��Ϣ ��λ        //
// 2007/07/11 �����ֹ�(searchPartsNo)��LIKE�����ɲá�                       //
// 2007/07/24 ��ͭ��λ���򥻥å������ɲ�(�ե��륿����ǽ)targetHoldMonth //
// 2016/06/24 CSV���ϤΤ���SQL��Where��������                        ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class InventoryAverage_Controller
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
        $this->model = new InventoryAverage_Model($this->request);
    }
    
    ///// MVC �� Model�� �¹ԥ��å����ؤν���
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->commentSave($this->request, $this->result, $this->session);
            break;
        case 'EditFactor':                                  // �װ��ޥ��������Խ�
            $this->model->editFactor($this->request, $this->result, $this->session);
            break;
        case 'DeleteFactor':                                // �װ��ޥ������κ��
            $this->model->deleteFactor($this->request, $this->result, $this->session);
            break;
        case 'ActiveFactor':                                // �װ��ޥ������κ��
            $this->model->activeFactor($this->request, $this->result, $this->session);
            break;
        }
    }
    
    ///// MVC View�������ؽ���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('inventaverage');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
        case 'Both':                                        // �ե������Ajax��List(ViewCondForm.php�ǽ���)
            $this->viewCondFormExecute($this->menu, $this->request, $uniq);
            break;
        case 'List':                                        // Ajax�� Listɽ��
            $this->viewListExecute($this->menu, $this->request, $this->model, $this->session, $uniq);
            break;
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->viewEditCommentExecute($this->menu, $this->request, $this->model, $this->result, $uniq);
            break;
        case 'FactorMnt':                                   // �װ��ޥ������ξȲ��Խ�
            $this->viewEditFactorExecute($this->menu, $this->request, $this->model, $this->session, $uniq);
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
        $this->InitPageKeep();
        // showMenu�ν���
        $this->InitShowMenu();
        // searchPartsNo�ν���
        $this->InitSearchPartsNo();
        // targetDivision�ν���
        $this->InitTargetDivision();
        // targetHoldMonth�ν���
        $this->InitTargetHoldMonth();
        // targetSortItem�ν���
        $this->InitTargetSortItem();
        // targetPartsNo�ν��� �����ȤξȲ��Խ���
        $this->InitTargetPartsNo();
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ������Ȳ񤷤���������ͤ�����å�
    // page_keep���������rec_no �ڤӥڡ�������ν���
    private function InitPageKeep()
    {
        if ($this->request->get('page_keep') != '') {
            $this->request->add('showMenu', 'Both');
            // ����å������Ԥ˥ޡ�������
            if ($this->session->get_local('rec_no') != '') {
                $this->request->add('rec_no', $this->session->get_local('rec_no'));
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
    
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu()
    {
        $showMenu = $this->request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // ���꤬�ʤ�����Condition Form (�������)
        }
        $this->request->add('showMenu', $showMenu);
    }
    
    ///// �����ֹ�μ����������
    // searchPartsNo�ν���
    private function InitSearchPartsNo()
    {
        if ($this->request->get('searchPartsNo') == '') {
            return true;
        }
        if (strlen($this->request->get('searchPartsNo')) > 9) return false; else return true;
    }
    
    ///// ���ʶ�ʬ�μ����������
    // targetDivision�ν���
    private function InitTargetDivision()
    {
        $targetDivision = $this->request->get('targetDivision');
        if ($targetDivision == '') {
            if ($this->session->get_local('targetDivision') == '') {
                $targetDivision = 'AL';                     // ���꤬�ʤ���������(����)
            } else {
                $targetDivision = $this->session->get_local('targetDivision');
            }
        }
        $this->session->add_local('targetDivision', $targetDivision);
        $this->request->add('targetDivision', $targetDivision);
        if (is_numeric($this->request->get('targetDivision'))) {
            $this->error = 1;
            $this->errorMsg = '���ʥ��롼�פλ���ϥ���ե��٥å���ʸ����ʸ���Ǥ���';
            return false;
        }
        if (strlen($this->request->get('targetDivision')) != 2) {
            $this->error = 1;
            $this->errorMsg = '���ʥ��롼�פλ���ϥ���ե��٥å���ʸ����ʸ���Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ��ͭ��λ���
    // targetHoldMonth�ν���
    private function InitTargetHoldMonth()
    {
        $targetHoldMonth = $this->request->get('targetHoldMonth');
        if ($targetHoldMonth == '') {
            if ($this->session->get_local('targetHoldMonth') == '') {
                $targetHoldMonth = '0';             // ���꤬�ʤ�������ͭ�����ꤷ�ʤ�
            } else {
                $targetHoldMonth = $this->session->get_local('targetHoldMonth');
            }
        }
        $this->session->add_local('targetHoldMonth', $targetHoldMonth);
        $this->request->add('targetHoldMonth', $targetHoldMonth);
    }
    
    ///// �������оݹ��ܤμ����������
    // targetSortItem�ν���
    private function InitTargetSortItem()
    {
        $targetSortItem = $this->request->get('targetSortItem');
        if ($targetSortItem == '') {
            if ($this->session->get_local('targetSortItem') == '') {
                $targetSortItem = 'money';                     // ���꤬�ʤ����Ϻ߸˶��
            } else {
                $targetSortItem = $this->session->get_local('targetSortItem');
            }
        } else {
            ///// ����Ū�˥ꥹ�Ȥˤ��롣
            $this->request->add('showMenu', 'Both');
        }
        $this->session->add_local('targetSortItem', $targetSortItem);
        $this->request->add('targetSortItem', $targetSortItem);
    }
    
    ///// ������Υ����ȤξȲ��Խ��� �����ֹ�ѥ�᡼�� ����������
    // targetPartsNo�ν���
    private function InitTargetPartsNo()
    {
        ;
        if ($this->request->get('targetPartsNo') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (!is_numeric(substr($this->request->get('targetPartsNo'), 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�Σ��夫�飷��ޤǤϿ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetPartsNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�ϣ���Ǥ���';
            return false;
        }
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// Condition Form ��ɽ�� �� Both(Ajax��ξ���¹�)�����
    private function viewCondFormExecute($menu, $request, $uniq)
    {
        require_once ('inventory_average_ViewCondForm.php');
    }
    
    ///// ����ɽ��
    private function viewListExecute($menu, $request, $model, $session, $uniq)
    {
        $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
        $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
        $model->outViewListHTML($request, $menu, $pageParameter, $session);
        $csv_where   = $session->get('csv_where');
        $pageControl = mb_convert_encoding($pageControl, 'UTF-8');
        // $pageParameter = mb_convert_encoding($pageParameter, 'UTF-8');
        require_once ('inventory_average_ViewList.php');
    }
    
    ///// �����Ȥ��Խ�������ɥ�ɽ��
    private function viewEditCommentExecute($menu, $request, $model, $result, $uniq)
    {
        $model->getComment($this->request, $this->result);
        $model->getFactorOptions($this->request, $this->result);
        require_once ('inventory_average_ViewEditComment.php');
    }
    
    ///// �װ��ޥ��������Խ�������ɥ�ɽ��
    private function viewEditFactorExecute($menu, $request, $model, $session, $uniq)
    {
        $model->outViewFactorHTML($request, $menu, $session);
        require_once ('inventory_average_ViewEditFactor.php');
    }
    
} // class InventoryAverage_Controller End

?>
