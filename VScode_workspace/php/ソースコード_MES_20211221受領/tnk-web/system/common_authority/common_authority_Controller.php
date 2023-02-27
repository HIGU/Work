<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ���� �ط��ơ��֥� ���ƥʥ�                   MVC Controller ��  //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/07/15 Created   common_authority_Controller.php                     //
// 2006/09/06 ����̾�ν�����ǽ�ɲä�ȼ�� edit/updateDivision  �ط����ɲ�    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// require_once ('../../tnk_func.php');        // workingDayOffset(-1), day_off(), date_offset() �ǻ���
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class CommonAuthority_Controller
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
        $this->model = new CommonAuthority_Model($this->request);
        
        //////////// �֥饦�����Υ���å����к���
        $this->uniq = $this->menu->set_useNotCache('common_authority');
    }
    
    ///// MVC �� Model�� �¹ԥ��å��ν���
    public function Execute()
    {
        switch ($this->request->get('Action')) {
        case 'ListDivision':                               // ���¶�ʬ�ꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'ListDivHeader':                              // ���¶�ʬ�إå����ꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivHeader');
            break;
        case 'ListDivBody':                                // ���¶�ʬ�ܥǥ��ꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivBody');
            break;
        case 'ListID':                                     // ����ID�Υꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'ListIDHeader':                               // ����ID�Υإå����ꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListIDHeader');
            break;
        case 'ListIDBody':                                  // ����ID�Υܥǥ��ꥹ�Ȼؼ�
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListIDBody');
            break;
        case 'AddDivision':                                 // ���� ���¶�ʬ �ɲ�
            $this->model->addDivision($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'DeleteDivision':                              // ���� ���¶�ʬ ���
            $this->model->deleteDivision($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'EditDivision':                                // ����̾ ����
            $this->model->editDivision($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'UpdateDivision':                              // ����̾ ������Ͽ
            $this->model->updateDivision($this->request, $this->result);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListDivision');
            break;
        case 'AddID':                                       // ���� ����ID �ɲ�
            $this->model->addID($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'DeleteID':                                    // ���� ����ID ���
            $this->model->deleteID($this->request);
            if ($this->request->get('showMenu') == '') $this->request->add('showMenu', 'ListID');
            break;
        case 'ConfirmID':                                   // ���� ����ID ��Ͽ���γ�ǧ
            break;
        default:
            // showMenu�ν���
            $this->InitShowMenu($this->request);
        }
    }
    
    ///// MVC View���ν���
    public function display()
    {
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'StartForm':                                   // ���ܥڡ�����ɽ��
            $this->viewStartFormExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivision':                                // Ajax�� ���¶�ʬ�Υꥹ��ɽ��
            $this->viewListDivisionExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivHeader':                               // ���¶�ʬ�Υإå����ꥹ��ɽ��
            $this->viewListDivHeaderExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListDivBody':                                 // ���¶�ʬ�Υܥǥ��ꥹ��ɽ��
            $this->viewListDivBodyExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListID':                                      // Ajax�� ���ꤵ�줿��ʬ�Ǥ�ID�ꥹ��ɽ��
            $this->viewListIDExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListIDHeader':                                // ���ꤵ�줿��ʬ�Ǥ�ID�إå����ꥹ��ɽ��
            $this->viewListIDHeaderExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListIDBody':                                  // ���ꤵ�줿��ʬ�Ǥ�ID�ܥǥ��ꥹ��ɽ��
            $this->viewListIDBodyExecute($this->menu, $this->request, $this->model, $this->result, $this->uniq);
            break;
        case 'ListCategory':                                // ���ꤵ�줿ID��Category�ꥹ��ɽ��
            echo $this->model->categorySelectList($this->model->getCategory($this->request));
            break;
        case 'GetIDName':                                   // ���ꤵ�줿ID��Category�����Ƥ�ɽ��
            echo $this->model->getIDName($this->request);
            break;
        default:                                            // �����ʥꥯ�����Ȥ��б�ͽ��
            echo '�����ʥꥯ�����ȤǤ���';
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
        
        // targetDivision�ν���
        $this->InitTargetDivision($this->request);
        // targetID�ν���
        $this->InitTargetID($this->request);
        // targetAuthName�ν���
        $this->InitTargetAuthName($this->request);
        
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'StartForm');         // ���꤬�ʤ����ϸ��¥ޥ������ΰ���
        }
    }
    
    ///// ���¥ޥ������ζ�ʬ������� �� ����
    // targetDivision�ν���
    private function InitTargetDivision($request)
    {
        if ($request->get('targetDivision') == '') return true;
        if ($request->get('targetDivision') >= 1 && $request->get('targetDivision') <= 32000) {
            return true;
        }
        $this->error = 1;
        $this->errorMsg = '���¥ޥ������λ��꤬�����Ǥ���';
        return false;
    }
    
    ///// ���¤Υ��С�������� �� ����
    // targetID�ν���
    private function InitTargetID($request)
    {
        if ($request->get('targetID') == '') return true;
        return true;    // targetID �ϲ��Ǥ����ϲ�ǽ�Ȥ���
        
        if ($request->get('targetID') >= 1 && $request->get('targetID') <= 32000) {
            return true;
        }
        $this->error = 1;
        $this->errorMsg = '���¥��С��λ��꤬�����Ǥ���';
        return false;
    }
    
    ///// ����̾����Ͽ�ǡ�����ʸ���������Ѵ�
    // targetAuthName�ν���
    private function InitTargetAuthName($request)
    {
        if ($request->get('targetAuthName') == '') return true;
        // $targetAuthName = mb_convert_encoding(stripslashes($_REQUEST['targetAuthName']), 'EUC-JP', 'SJIS');
        $request->add('targetAuthName', mb_convert_encoding($request->get('targetAuthName'), 'EUC-JP', 'SJIS'));
        return true;
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// ���¥ޥ������ΰ���ɽ��
    private function viewStartFormExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewStartForm.php');
    }
    
    ///// ���¥ޥ������ΰ���ɽ��
    private function viewListDivisionExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewListDivision.php');
    }
    
    ///// ���¥ޥ������Υإå���ɽ��
    private function viewListDivHeaderExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewDivHeader.php');
    }
    
    ///// ���¥ޥ������Υܥǥ�ɽ��
    private function viewListDivBodyExecute($menu, $request, $model, $result, $uniq)
    {
        $rows = $model->getViewListDivision($this->request, $res);
        require_once ('common_authority_ViewDivBody.php');
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ���¤Υ��С�����ɽ��
    private function viewListIDExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewListID.php');
    }
    
    ///// ���¥��С��Υإå���ɽ��
    private function viewListIDHeaderExecute($menu, $request, $model, $result, $uniq)
    {
        require_once ('common_authority_ViewIDHeader.php');
    }
    
    ///// ���¥��С��Υܥǥ�ɽ��
    private function viewListIDBodyExecute($menu, $request, $model, $result, $uniq)
    {
        $rows = $model->getViewListID($this->request, $res);
        require_once ('common_authority_ViewIDBody.php');
    }
    
} // class CommonAuthority_Controller End

?>
