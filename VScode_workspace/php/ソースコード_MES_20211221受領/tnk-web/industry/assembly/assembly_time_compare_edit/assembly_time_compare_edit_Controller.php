<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�δ������������ӹ�������Ͽ���������            MVC Controller ��  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/07 Created   assembly_time_compare_Controller.php                //
// 2006/03/13 ���ʶ�ʬ������Ȥ��� targetDivision ���ɲ�                    //
// 2006/05/01 �����ȾȲ��Խ����å����ɲ�                              //
// 2006/05/08 �����ȤξȲ��Խ��ѥơ��֥�Υ����������ֹ梪�ײ��ֹ���ѹ�//
// 2006/05/10 ���ȡ���ư������������ �̤˾Ȳ񥪥ץ������ɲ�           //
// 2006/05/15 InitTargetPlanNo()���� ; �����ιԤ����ä��Τ���             //
// 2006/08/31 ���ܥ����ȵ�ǽ �ɲäˤ�� InitTargetSortItem() �᥽�åɤ���� //
// 2007/06/12 ��������ϿWindow����ƥ�����ɥ��β��̹����б���commentSave //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyTimeCompareEdit_Controller
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
        $this->model = new AssemblyTimeCompareEdit_Model($this->request);
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('assyTimeComp');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
        case 'Both':                                        // �ե������Ajax��List(ViewCondForm.php�ǽ���)
            require_once ('assembly_time_compare_edit_ViewCondForm.php');
            break;
        case 'CommentSave':                                 // �����Ȥ���¸
            $this->model->commentSave($this->request, $this->result, $this->session);
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('assembly_time_compare_edit_ViewEditComment.php');
            break;
        case 'List':                                        // Ajax�� Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu, $this->session);
            require_once ('assembly_time_compare_edit_ViewList.php');
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
        // targetDateStr�ν���
        $this->InitTargetDateStr();
        // targetDateEnd�ν���
        $this->InitTargetDateEnd();
        // targetDivision�ν���
        $this->InitTargetDivision();
        // targetProcess�ν���
        $this->InitTargetProcess();
        // targetPlanNo�ν��� �����ȤξȲ��Խ���
        $this->InitTargetPlanNo();
        // targetAssyNo�ν��� �����ȤξȲ��Խ���
        $this->InitTargetAssyNo();
        // targetSortItem�ν���
        $this->InitTargetSortItem();
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
            $this->request->add('showMenu', 'List');
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
    }
    
    ///// ������ʬ�μ����������
    // targetProcess�ν���
    private function InitTargetProcess()
    {
        $targetProcess = $this->request->get('targetProcess');
        if ($targetProcess == '') {
            if ($this->session->get_local('targetProcess') == '') {
                $targetProcess = 'H';                       // ���꤬�ʤ����ϼ��ȹ���
            } else {
                $targetProcess = $this->session->get_local('targetProcess');
            }
        }
        $this->session->add_local('targetProcess', $targetProcess);
        $this->request->add('targetProcess', $targetProcess);
    }
    
    ///// �ײ��ֹ���Υ����ȤξȲ��Խ��� �ײ��ֹ�ѥ�᡼�� ����������
    // targetPlanNo�ν���
    private function InitTargetPlanNo()
    {
        if ($this->request->get('targetPlanNo') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (!is_numeric(substr($this->request->get('targetPlanNo'), 1, 7))) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�Σ��夫�飸��ޤǤϿ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetPlanNo')) != 8) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�ϣ���Ǥ���';
            return false;
        }
        return true;
    }
    
    ///// ������Υ����ȤξȲ��Խ��� �����ֹ�ѥ�᡼�� ����������
    // targetAssyNo�ν���
    private function InitTargetAssyNo()
    {
        if ($this->request->get('targetAssyNo') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (!is_numeric(substr($this->request->get('targetAssyNo'), 2, 5))) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�Σ��夫�飷��ޤǤϿ��������Ϥ��Ʋ�������';
            return false;
        }
        if (strlen($this->request->get('targetAssyNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�ϣ���Ǥ���';
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
                $targetSortItem = 'line';                     // ���꤬�ʤ����ϥ饤�󥰥롼��
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
    
} // class AssemblyTimeCompareEdit_Controller End

?>
