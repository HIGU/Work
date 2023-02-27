<?php
//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤξȲ� �ǽ�����������Ǹ��ߺ߸ˤ�����ʪ   MVC Controller ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_Controller.php                   //
// 2006/04/06 ����иˤ��ϰϵڤӲ��(ʪ��ư��)�ξ�索�ץ��������        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class LongHoldingParts_Controller
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
        $this->model = new LongHoldingParts_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('longHolding');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
        case 'Both':                                        // �ե������Ajax��List(ViewCondForm.php�ǽ���)
            require_once ('long_holding_parts_ViewCondForm.php');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->commentSave($this->request);
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('long_holding_parts_ViewEditComment.php');
            break;
        case 'List':                                        // Ajax�� Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            require_once ('long_holding_parts_ViewList.php');
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
        // targetDate�ν���
        $this->InitTargetDate();
        // targetDateSpan�ν���
        $this->InitTargetDateSpan();
        // targetDivision�ν���
        $this->InitTargetDivision();
        // targetSortItem�ν���
        $this->InitTargetSortItem();
        // targetOutFlg�ν���
        $this->InitTargetOutFlg();
        // targetOutDate�ν���
        $this->InitTargetOutDate();
        // targetOutCount�ν���
        $this->InitTargetOutCount();
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
    
    ///// �о� �ǽ� ��������ǯ���� �����������
    // targetDate�ν���
    private function InitTargetDate()
    {
        $targetDate = $this->request->get('targetDate');
        if ($targetDate == '') {
            if ($this->session->get_local('targetDate') == '') {
                $targetDate = 24;       // ���꤬�ʤ����ϣ���������
            } else {
                $targetDate = $this->session->get_local('targetDate');
            }
        }
        $this->session->add_local('targetDate', $targetDate);
        $this->request->add('targetDate', $targetDate);
        if (!is_numeric($this->request->get('targetDate'))) {
            $this->error = 1;
            $this->errorMsg = '�ǽ��������λ���Ͽ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetDate')) != 2) {
            $this->error = 1;
            $this->errorMsg = '�ǽ��������η���ϣ���Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// �о� �ǽ� ��������ǯ���� �����������
    // targetDateSpan�ν���
    private function InitTargetDateSpan()
    {
        $targetDateSpan = $this->request->get('targetDateSpan');
        if ($targetDateSpan == '') {
            if ($this->session->get_local('targetDateSpan') == '') {
                $targetDateSpan = 120;      // ���꤬�ʤ�����120����ʬ(�Ǹ�ޤ�)
            } else {
                $targetDateSpan = $this->session->get_local('targetDateSpan');
            }
        }
        $this->session->add_local('targetDateSpan', $targetDateSpan);
        $this->request->add('targetDateSpan', $targetDateSpan);
        if (!is_numeric($this->request->get('targetDateSpan'))) {
            $this->error = 1;
            $this->errorMsg = '�ǽ�������������ϰϻ���Ͽ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetDateSpan')) > 3) {
            $this->error = 1;
            $this->errorMsg = '�ǽ�������������ϰϷ���Ϸ���ϣ���ޤǤǤ���';
            return false;
        }
        return true;
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
                $targetSortItem = 'price';                     // ���꤬�ʤ����϶��
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
    
    ///// ���ץ����ǽи������и˲���������������checkbox�ѥե饰 �����������
    // targetOutFlg�ν���
    private function InitTargetOutFlg()
    {
        $targetOutFlg = $this->request->get('targetOutFlg');
        if ($targetOutFlg == '') {
            if ($this->session->get_local('targetOutFlg') == '') {
                $targetOutFlg = 'off';       // ���꤬�ʤ����ϥ����å��ʤ�
            } else {
                $targetOutFlg = $this->session->get_local('targetOutFlg');
            }
        }
        $this->session->add_local('targetOutFlg', $targetOutFlg);
        $this->request->add('targetOutFlg', $targetOutFlg);
        return true;
    }
    
    ///// �и����β����������λ��� �����������
    // targetOutDate�ν���
    private function InitTargetOutDate()
    {
        $targetOutDate = $this->request->get('targetOutDate');
        if ($targetOutDate == '') {
            if ($this->session->get_local('targetOutDate') == '') {
                $targetOutDate = '24';      // ���꤬�ʤ����ϣ���������
            } else {
                $targetOutDate = $this->session->get_local('targetOutDate');
            }
        }
        $this->session->add_local('targetOutDate', $targetOutDate);
        $this->request->add('targetOutDate', $targetOutDate);
        if (!is_numeric($this->request->get('targetOutDate'))) {
            $this->error = 1;
            $this->errorMsg = '�и����η������Ͽ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetOutDate')) != 2) {
            $this->error = 1;
            $this->errorMsg = '�и����η���ϣ���Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ����иˤβ������ �����������
    // targetOutCount�ν���
    private function InitTargetOutCount()
    {
        $targetOutCount = $this->request->get('targetOutCount');
        if ($targetOutCount == '') {
            if ($this->session->get_local('targetOutCount') == '') {
                $targetOutCount = '0';          // ���꤬�ʤ����ϣ���(ư����̵�����)
            } else {
                $targetOutCount = $this->session->get_local('targetOutCount');
            }
        }
        $this->session->add_local('targetOutCount', $targetOutCount);
        $this->request->add('targetOutCount', $targetOutCount);
        if (!is_numeric($this->request->get('targetOutCount'))) {
            $this->error = 1;
            $this->errorMsg = '����иˤβ������Ͽ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetOutCount')) != 1) {
            $this->error = 1;
            $this->errorMsg = '����иˤβ���ϣ���Ǥ���';
            return false;
        }
        return true;
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
    
} // class LongHoldingParts_Controller End

?>
