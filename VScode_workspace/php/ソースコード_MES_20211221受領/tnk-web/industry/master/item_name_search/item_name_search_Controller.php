<?php
//////////////////////////////////////////////////////////////////////////////
// �����ƥ�ޥ���������̾�ˤ��������������ʬ����        MVC Controller ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_Controller.php                     //
// 2006/05/22 ����ˤ��ޥ������������ɲ� targetItemMaterial               //
// 2006/05/23 �߸˥����å����ץ������ɲ� targetStockOption                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ItemNameSearch_Controller
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
        $this->model = new ItemNameSearch_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('itemName');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
        case 'Both':                                        // �ե������Ajax��List(ViewCondForm.php�ǽ���)
            require_once ('item_name_search_ViewCondForm.php');
            break;
        case 'List':                                        // Ajax�� Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            require_once ('item_name_search_ViewList.php');
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
        // targetDivision�ν���
        $this->InitTargetDivision();
        // targetSortItem�ν���
        $this->InitTargetSortItem();
        // targetItemName�ν���
        $this->InitTargetItemName();
        // targetItemMaterial�ν���
        $this->InitTargetItemMaterial();
        // targetStockOption�ν���
        $this->InitTargetStockOption();
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
    
    ///// �������оݹ��ܤμ����������
    // targetSortItem�ν���
    private function InitTargetSortItem()
    {
        $targetSortItem = $this->request->get('targetSortItem');
        if ($targetSortItem == '') {
            if ($this->session->get_local('targetSortItem') == '') {
                $targetSortItem = 'noData';                     // ���꤬�ʤ����϶��
            } else {
                $targetSortItem = $this->session->get_local('targetSortItem');
            }
        } else {
            ///// ����Ū�˥ꥹ�Ȥˤ��롣
            $this->request->add('showMenu', 'Both');
            if ($targetSortItem == $this->session->get_local('targetSortItem')) {
                $targetSortItem = 'noData';     // Ʊ���ʤ�ȥ���ˤ����Ǿä�
            }
        }
        $this->session->add_local('targetSortItem', $targetSortItem);
        // noData�ʤ�֥�󥯤ˤ��ƥꥯ�����Ȥ˽񤭹���
        if ($targetSortItem == 'noData') $targetSortItem = '';
        $this->request->add('targetSortItem', $targetSortItem);
    }
    
    ///// ��̾�ˤ�븡���ǡ��� �����������
    // targetItemName�ν���
    private function InitTargetItemName()
    {
        // Ajax���������줿���Х���ʸ����SJIS��EUC-JP���Ѵ�
        $targetItemName = mb_convert_encoding($this->request->get('targetItemName'), 'EUC-JP', 'SJIS');
        // Ⱦ�ѥ��ʤ����ѥ���(������ʸ��)���Ѵ�
        $targetItemName = mb_convert_kana($targetItemName, 'KV');
        // �ꥯ������ͭ��̵�������å�
        if ($targetItemName == '') {
            // �������褫��������Υ����å�
            if ($this->request->get('showMenu') == 'Both') {
                // ���å����ǡ�����ͭ��̵�������å�
                if ($this->session->get_local('targetItemName') == 'noData') {
                    $targetItemName = 'noData';       // ���꤬�ʤ����֥�󥯤������noData��񤭹���
                } else {
                    $targetItemName = $this->session->get_local('targetItemName');
                }
            }
        }
        $this->session->add_local('targetItemName', $targetItemName);
        // noData�ʤ�֥�󥯤ˤ��ƥꥯ�����ȥǡ������᤹
        if ($targetItemName == 'noData') $targetItemName = '';
        $this->request->add('targetItemName', $targetItemName);
        return true;
    }
    
    ///// ����ˤ�븡���ǡ��� �����������
    // targetItemMaterial�ν���
    private function InitTargetItemMaterial()
    {
        // Ajax���������줿���Х���ʸ����SJIS��EUC-JP���Ѵ�
        $targetItemMaterial = mb_convert_encoding($this->request->get('targetItemMaterial'), 'EUC-JP', 'SJIS');
        // Ⱦ�ѥ��ʤ����ѥ���(������ʸ��)���Ѵ�
        $targetItemMaterial = mb_convert_kana($targetItemMaterial, 'KV');
        // �ꥯ������ͭ��̵�������å�
        if ($targetItemMaterial == '') {
            // �������褫��������Υ����å�
            if ($this->request->get('showMenu') == 'Both') {
                // ���å����ǡ�����ͭ��̵�������å�
                if ($this->session->get_local('targetItemMaterial') == 'noData') {
                    $targetItemMaterial = 'noData';       // ���꤬�ʤ����֥�󥯤������noData��񤭹���
                } else {
                    $targetItemMaterial = $this->session->get_local('targetItemMaterial');
                }
            }
        }
        $this->session->add_local('targetItemMaterial', $targetItemMaterial);
        // noData�ʤ�֥�󥯤ˤ��ƥꥯ�����ȥǡ������᤹
        if ($targetItemMaterial == 'noData') $targetItemMaterial = '';
        $this->request->add('targetItemMaterial', $targetItemMaterial);
        return true;
    }
    
    ///// �߸˥����å����ץ����μ����������
    // targetStockOption�ν���
    private function InitTargetStockOption()
    {
        ///// targetStockOption��Ŭ�������å�
        switch ($this->request->get('targetStockOption')) {
        case '3':     // ���ߺ߸ˤ���
        case '2':     // �߸˷��򤢤�
        case '1':     // �߸˥ޥ���������
        case '0':     // �߸ˤ�̵�뤹��
            break;
        default:
            if ($this->session->get_local('targetStockOption') != '') {
                // ���å����ǡ���������
                $this->request->add('targetStockOption', $this->session->get_local('targetStockOption'));
            } else {
                $this->request->add('targetStockOption', 0);  // �����
            }
        }
        $this->session->add_local('targetStockOption', $this->request->get('targetStockOption'));
        return true;
    }
    
} // class ItemNameSearch_Controller End

?>
