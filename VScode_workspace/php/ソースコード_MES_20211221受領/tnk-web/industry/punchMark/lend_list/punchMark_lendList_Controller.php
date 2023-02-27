<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �߽���Ģ��˥塼                     MVC Controller ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList_Controller.php                   //
// 2007/11/26 LendRegist(�߽Хե�����)�ɲá�Init()���߽��衦ô���Ԥ��ɲ�    //
// 2007/12/03 �߽���targetLendDate���ɲ� $model��sql��Ϣ�᥽�åɤ�display�� //
// 2007/12/05 �߽�ɼ�ΰ��� LendPrintExecute()�᥽�åɤ��ɲ�                 //
//////////////////////////////////////////////////////////////////////////////

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PunchMarkLendList_Controller
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
        $this->model = new PunchMarkLendList_Model();
    }
    
    ///// MVC Control�� �¹ԥ��å����ؤν���
    public function execute()
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showMenu ���Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init($this->request, $this->session);
        ///// �ꥯ�����ȤΥ�����������
        switch ($this->request->get('Action')) {
        case 'MarkSearch':                                  // �����¹�(���)
            $this->model->setMarkWhere($this->session);
            $this->model->setMarkSQL($this->session);
            break;
        case 'LendSearch':                                  // �߽���Ģ
            $this->model->setLendWhere($this->session);
            $this->model->setLendOrder($this->session);
            $this->model->setLendSQL($this->session);
            break;
        case 'LendRegist':                                  // �߽Хե�����ǡ�������
            $this->model->getLend($this->session, $this->result);
            break;
        case 'Lend':                                        // �߽м¹�
            $this->model->setLend($this->session);
            break;
        case 'LendCancel':                                  // �߽Фμ��
            $this->model->setLendCancel($this->session);
            break;
        case 'Return':                                      // �ֵѼ¹�
            $this->model->setReturn($this->session);
            break;
        case 'ReturnCancel':                                // �ֵѤμ��
            $this->model->setReturnCancel($this->session);
            break;
        }
    }
    
    ///// MVC Control�� View���ؤν���
    public function display()
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $this->menu->set_useNotCache('punchMarkLendList');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $this->menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        switch ($this->request->get('showMenu')) {
        case 'CondForm':                                    // �������ե�����
            $this->CondFormExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'MarkList':                                    // Ajax�� �ꥹ��ɽ��
        case 'MarkListWin':                                 // Ajax�� �̥�����ɥ���Listɽ��
            $this->model->setMarkWhere($this->session);
            $this->model->setMarkSQL($this->session);
            $this->model->outViewMarkListHTML($this->session, $this->menu);
            $this->ViewMarkListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'LendList':                                    // Ajax�� �ꥹ��ɽ��
        case 'LendListWin':                                 // Ajax�� �̥�����ɥ���Listɽ��
            $this->model->setLendWhere($this->session);
            $this->model->setLendOrder($this->session);
            $this->model->setLendSQL($this->session);
            $this->model->outViewLendListHTML($this->session, $this->menu);
            $this->ViewLendListExecute($this->menu, $this->session, $this->model, $this->request, $uniq);
            break;
        case 'LendRegistForm':                              // �߽���Ͽ�ե�����
            $this->LendRegistFormExecute($this->menu, $this->session, $this->result, $this->request, $uniq);
            break;
        case 'LendPrint':                                   // ����߽�ɼ�ΰ���
            $this->LendPrintExecute($this->menu, $this->session, $this->model);
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
        
        // �����ֹ�ν���
        $this->InitTargetPartsNo($request, $session);
        // ��������ɤν���
        $this->InitTargetMarkCode($request, $session);
        // ê�֤ν���
        $this->InitTargetShelfNo($request, $session);
        // ���ͤν���
        $this->InitTargetNote($request, $session);
        // �߽���ν���(��Ͽ���˻���)
        $this->InitTargetVendor($request, $session);
        // ô���Ԥν���(��Ͽ���˻���)
        $this->InitTargetLendUser($request, $session);
        // �߽����ν���(�ֵѤ���Ͽ���˻���)
        $this->InitTargetLendDate($request, $session);
        
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
        // ����Ͻ�����ɬ�פʤ��Τǲ��⤷�ʤ�
        return;
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
    
    ///// �����ֹ�μ����������
    private function InitTargetPartsNo($request, $session)
    {
        if ($request->get('targetPartsNo') == '') {
            if ($session->get_local('targetPartsNo') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetPartsNo', '');
            }
        } else {
            // ���������ɻ�
            $request->add('targetPartsNo', mb_convert_kana($request->get('targetPartsNo'), 'a'));
            $session->add_local('targetPartsNo', $request->get('targetPartsNo'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ��������ɤμ����������
    private function InitTargetMarkCode($request, $session)
    {
        if ($request->get('targetMarkCode') == '') {
            if ($session->get_local('targetMarkCode') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetMarkCode', '');
            }
        } else {
            // ���������ɻ�
            $request->add('targetMarkCode', mb_convert_kana($request->get('targetMarkCode'), 'a'));
            $session->add_local('targetMarkCode', $request->get('targetMarkCode'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ê�֤μ����������
    private function InitTargetShelfNo($request, $session)
    {
        if ($request->get('targetShelfNo') == '') {
            if ($session->get_local('targetShelfNo') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetShelfNo', '');
            }
        } else {
            // ���������ɻ�
            $request->add('targetShelfNo', mb_convert_kana($request->get('targetShelfNo'), 'a'));
            $session->add_local('targetShelfNo', $request->get('targetShelfNo'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ���ͤμ����������
    private function InitTargetNote($request, $session)
    {
        if ($request->get('targetNote') == '') {
            if ($session->get_local('targetNote') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetNote', '');
            }
        } else {
            // ���������ɻ�
            // $request->add('targetNote', mb_convert_kana($request->get('targetNote'), 'a'));
            $session->add_local('targetNote', $request->get('targetNote'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// �߽���μ���������� (��Ͽ��)
    private function InitTargetVendor($request, $session)
    {
        if ($request->get('targetVendor') == '') {
            if ($session->get_local('targetVendor') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetVendor', '');
            }
        } else {
            // ���������ɻ�
            $request->add('targetVendor', mb_convert_kana($request->get('targetVendor'), 'a'));
            $session->add_local('targetVendor', $request->get('targetVendor'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// ô���Ԥμ���������� (��Ͽ��)
    private function InitTargetLendUser($request, $session)
    {
        if ($request->get('targetLendUser') == '') {
            if ($session->get_local('targetLendUser') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetLendUser', '');
            }
        } else {
            // ���������ɻ�
            $request->add('targetLendUser', mb_convert_kana($request->get('targetLendUser'), 'a'));
            $session->add_local('targetLendUser', $request->get('targetLendUser'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    ///// �߽����μ���������� (��Ͽ��)
    private function InitTargetLendDate($request, $session)
    {
        if ($request->get('targetLendDate') == '') {
            if ($session->get_local('targetLendDate') == '') {
                return;     // ���̵꤬�����ϲ��⤷�ʤ�
            } else {
                // �ꥯ�����Ȥ��ʤ��ƥڡ��������ʳ��ʤ饻�å����򥯥ꥢ��
                if ($request->get('page_keep') == '') $session->add_local('targetLendDate', '');
            }
        } else {
            // ���������ɻ� �� ���饤����Ȥ���Υǡ������ϤϤʤ����ᥳ���ȥ�����
            // $request->add('targetLendDate', mb_convert_kana($request->get('targetLendDate'), 'a'));
            $session->add_local('targetLendDate', $request->get('targetLendDate'));
        }
        // ���顼�����å���ɬ�פʥꥯ�����ȤϤ����˵���
    }
    
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $session, $model, $request, $uniq)
    {
        require_once ('punchMark_lendList_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    ///// ��� ������� �ꥹ��
    private function ViewMarkListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'MarkList':
            require_once ('punchMark_markList_ViewListAjax.php');
            break;
        case 'MarkListWin':
        default:
            require_once ('punchMark_markList_ViewListWin.php');
        }
        return true;
    }
    
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    ///// �߽���Ģ
    private function ViewLendListExecute($menu, $session, $model, $request, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'LendList':
            require_once ('punchMark_lendList_ViewListAjax.php');
            break;
        case 'LendListWin':
        default:
            require_once ('punchMark_lendList_ViewListWin.php');
        }
        return true;
    }
    
    ///// �߽м¹�(��Ͽ)�ե�����
    private function LendRegistFormExecute($menu, $session, $result, $request, $uniq)
    {
        require_once ('punchMark_lendList_ViewLendRegist.php');
        return true;
    }
    
    ///// ����߽�ɼ�ΰ���
    private function LendPrintExecute($menu, $session, $model)
    {
        $model->lendPrint($menu, $session);
        return true;
    }
    
} // class PunchMarkLendList_Controller End

?>
