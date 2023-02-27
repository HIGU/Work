<?php
//////////////////////////////////////////////////////////////////////////////
// ��ԡ�������ȯ��ν��� ��� �Ȳ�                      MVC Controller ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_Controller.php                   //
// 2007/12/20 �������پȲ���ɲ� Action=Details                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class TotalRepeatOrder_Controller
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
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new TotalRepeatOrder_Model($this->request);
    }
    
    ///// MVC �� Model�� �¹ԥ��å��ν���
    public function execute()
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ����
        $this->Init();
        
        if ($this->error) {
            $this->model->outListErrorMessage($this->session, $this->menu);
            return;
        }
        
        switch ($this->request->get('Action')) {
        case 'PageSet':                                     // �ꥯ�����ȤΥڡ�������
            $this->model->setWhere($this->session);
            $this->model->setLimit($this->session);
            $this->model->setSQL($this->session);
            $this->model->setTotal();
            $this->model->outListViewHTML($this->session, $this->menu);
            break;
        case 'Details':                                     // �ƹ��������٥ꥹ������(�ꥹ�Ȥ���2��Ū�˸ƽФ�)
            $this->model->setDetailsWhere($this->session);
            $this->model->setDetailsSQL($this->session);
            $this->model->setDetailsItem($this->session);
            $this->model->outDetailsViewHTML($this->session, $this->menu);
            break;
        default:
            // ���⤷�ʤ���
        }
    }
    
    ///// MVC View���ν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('totalRepeatOrder');
        
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
        // showMenu�ν���
        $this->InitShowMenu($this->request);
        
        // targetDateStr�ν���
        $this->InitTargetDateStr($this->request, $this->session);
        // targetDateEnd�ν���
        $this->InitTargetDateEnd($this->request, $this->session);
        // targetLimit�ν���
        $this->InitTargetLimit($this->request, $this->session);
        // targetVendor�ν���
        $this->InitTargetVendor($this->request, $this->session);
        // targetPartsNo�ν���
        $this->InitTargetPartsNo($this->request, $this->session);
        // targetProMark�ν���
        $this->InitTargetProMark($this->request, $this->session);
        
        // ���顼����
        if ($this->error) {
            $this->session->add('s_sysmsg', $this->errorMsg);
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
        if ($request->get('Action') == '') {
            // $request->add('Action', 'StartForm');       // ���꤬�ʤ����ϸ��¥ޥ������ΰ���
        }
    }
    
    ///// ��˥塼������ showMenu �Υǡ��������å� �� ����
    // showMenu�ν���
    private function InitShowMenu($request)
    {
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'CondForm');      // ���꤬�ʤ�����Condition Form (�������)
        }
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
        if (!$targetDateStr) return;
        if (!checkdate(substr($targetDateStr, 4, 2), substr($targetDateStr, 6, 2), substr($targetDateStr, 0, 4))) {
            $this->error++;
            $this->errorMsg = '�������դ�̵�������դǤ���';
            $session->add_local('targetDateStr', '');
        }
        return;
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
        if (!$targetDateEnd) return;
        if (!checkdate(substr($targetDateEnd, 4, 2), substr($targetDateEnd, 6, 2), substr($targetDateEnd, 0, 4))) {
            $this->error++;
            $this->errorMsg = '��λ���դ�̵�������դǤ���';
            $session->add_local('targetDateEnd', '');
        }
        return;
    }
    
    ///// ���Ǥ�ɽ���Կ��μ����������
    // targetLimit�ν���
    private function InitTargetLimit($request, $session)
    {
        $targetLimit = $request->get('targetLimit');
        if ($targetLimit == '') {
            if ($session->get_local('targetLimit') == '') {
                $targetLimit = ''; // workingDayOffset(-1);      // ���꤬�ʤ��������Ķ���
            } else {
                $targetLimit = $session->get_local('targetLimit');
            }
        }
        $session->add_local('targetLimit', $targetLimit);
        $request->add('targetLimit', $targetLimit);
        if (!$targetLimit) return;
        if (!is_numeric($targetLimit)) {
            $this->error++;
            $this->errorMsg = '�ڡ������Ͽ��������Ϥ��Ʋ�������';
            $session->add_local('targetLimit', '');
        }
        return;
    }
    
    ///// �������� �Ȳ���
    // targetVendor�ν���
    private function InitTargetVendor($request, $session)
    {
        $targetVendor = $request->get('targetVendor');
        if ($targetVendor == '') {
            if ($session->get_local('targetVendor') == '') {
                $targetVendor = '';
            } else {
                $targetVendor = $session->get_local('targetVendor');
            }
        }
        $session->add_local('targetVendor', $targetVendor);
        $request->add('targetVendor', $targetVendor);
        if (!$targetVendor) return;
        if (!is_numeric($targetVendor)) {
            $this->error++;
            $this->errorMsg = 'ȯ���襳���ɤϿ��������Ϥ��Ʋ�������';
            $session->add_local('targetVendor', '');
        }
        return;
    }
    
    // targetPartsNo�ν���
    private function InitTargetPartsNo($request, $session)
    {
        $targetPartsNo = $request->get('targetPartsNo');
        if ($targetPartsNo == '') {
            if ($session->get_local('targetPartsNo') == '') {
                $targetPartsNo = '';
            } else {
                $targetPartsNo = $session->get_local('targetPartsNo');
            }
        }
        $session->add_local('targetPartsNo', $targetPartsNo);
        $request->add('targetPartsNo', $targetPartsNo);
        if (!$targetPartsNo) return;
        if (strlen($targetPartsNo) != 9) {
            $this->error++;
            $this->errorMsg = '�����ֹ�ϣ���ɬ�פǤ���';
            $session->add_local('targetPartsNo', '');
        }
        return;
    }
    
    // targetProMark�ν���
    private function InitTargetProMark($request, $session)
    {
        $targetProMark = $request->get('targetProMark');
        if ($targetProMark == '') {
            if ($session->get_local('targetProMark') == '') {
                $targetProMark = '';
            } else {
                $targetProMark = $session->get_local('targetProMark');
            }
        }
        $session->add_local('targetProMark', $targetProMark);
        $request->add('targetProMark', $targetProMark);
        if (!$targetProMark) return;
        if (!ctype_alpha($targetProMark)) {
            $this->error++;
            $this->errorMsg = '��������ϱѻ������Ϥ��Ʋ�������';
            $session->add_local('targetProMark', '');
        }
        return;
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        require_once ('total_repeat_order_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $request, $result, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('total_repeat_order_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('total_repeat_order_ViewListWin.php');
        }
        return true;
    }
    
} // class TotalRepeatOrder_Controller End

?>
