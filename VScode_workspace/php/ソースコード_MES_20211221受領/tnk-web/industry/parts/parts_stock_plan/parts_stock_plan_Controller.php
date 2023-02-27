<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)               MVC Controller ��  //
// Copyright (C) 2006-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created   parts_stock_plan_Controller.php                     //
// 2006/08/01 ����ɽ���� /^[A-Z0-9]{7} �� /^[A-Z]{2}[0-9]{5} ���ѹ�         //
// 2007/02/08 �߸˷��򤫤�ƽФ��줿���Υ��������Ⱥ߸˷���ƽФ����ɲ�    //
//            CondFormExecute()��������ɲä��Ƥ���(�ǽ��Main�ǽ������ѹ�) //
// 2007/02/20 parts/����parts/parts_stock_history/parts_stock_view.php���ѹ�//
// 2007/02/21 CondFormExecute()�᥽�åɤ�noMenu(�����ͽ��α���������̵��) //
// 2007/03/09 �����ֹ�CP2A723-1 /^[A-Z]{2}[0-9]{5} �� /^[A-Z]{2}[A-Z0-9]{5} //
// 2007/03/22 parts_stock_view.php �� parts_stock_history_Main.php ���ѹ�   //
// 2007/07/27 $menu->set_retGet() �� $menu->set_retGET()�إߥ����ڥ�����    //
// 2017/06/28 A���Ȳ񤫤�θƤӽФ����б�                                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
require_once ('../../../tnk_func.php');     // workingDayOffset(-1), day_off(), date_offset() �ǻ���

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::prefix_Controller �� $obj = new Controller::prefix_Controller;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PartsStockPlan_Controller
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
        $this->model = new PartsStockPlan_Model($this->request);
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
            $this->CondFormExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'List':                                        // Ajax�� �ꥹ��ɽ��
        case 'ListWin':                                     // Ajax�� �̥�����ɥ���Listɽ��
            // $rows = $this->model->getViewListTable($this->request, $this->result);
            // $res  = $this->result->get_array();
            $this->model->outViewListHTML($this->request, $this->menu);
            $this->ViewListExecute($this->menu, $this->request, $this->model, $uniq);
            break;
        case 'CommentSave':                                 // �����Ȥ���¸ �饤���ֹ��ǯ����������
            $this->model->commentSave($this->request);
        case 'Comment':                                     // �̥�����ɥ��ǥ����ȤξȲ��Խ�
            $this->model->getComment($this->request, $this->result);
            require_once ('parts_stock_plan_ViewEditComment.php');
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
        // showMenu�ν���
        $this->InitShowMenu($this->request);
        // targetPartsNo�ν���
        $this->InitTargetPartsNo($this->request);
        // �������ʹ���ɽ����θƽ��б� �ν���
        $this->InitSetScno($this->request, $this->menu, $this->result, $this->session);
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
        ///// ����ϥڡ������椬̵������page_keep�ꥯ�����Ȥ�̵�뤹�롣
        return;
        if ($request->get('page_keep') != '') {
            $request->add('showMenu', 'List');  // �ڡ��������List����ɬ��
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
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            $showMenu = 'CondForm';         // ���꤬�ʤ�����Condition Form (�������)
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// �����ֹ�ѥ�᡼�� ����������
    // targetPartsNo�ν���
    private function InitTargetPartsNo($request)
    {
        if ($request->get('targetPartsNo') == '') {
            return true;          // ���꤬�ʤ����ϲ��⤷�ʤ���
        }
        if (strlen($request->get('targetPartsNo')) != 9) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ�ϣ���Ǥ���';
            return false;
        }
        // preg_match('/^[A-Z]{2}[0-9]{5}[-#]{1}[A-Z0-9]{1}$/', $request->get('targetPartsNo'));
                // �嵭�������ֹ��̿̾��§��Ĵ�٤�
        // ctype_alnum(substr($request->get('targetPartsNo'), 0, 7));
        // ctype_alpha();
        // ctype_digit();
        if (!preg_match('/^[A-Z]{2}[A-Z0-9]{5}[-#]{1}[A-Z0-9]{1}$/', $request->get('targetPartsNo'))) {
            $this->error = 1;
            $this->errorMsg = '�����ֹ椬�ְ�äƤ��ޤ���';
            return false;
        }
        return true;
    }
    
    ///// �������ʹ���ɽ����θƽ��б� ����
    // setMaterial�ν���
    private function InitSetScno($request, $menu, $result, $session)
    {
        if ($request->get('aden_flg') != '') {
            // allo_conf_parts_view.php�ǾȲ񤷤������ֹ��������뤿��
            $menu->set_retGET('aden_flg', $request->get('aden_flg'));
            $menu->set_retGET('sc_no', $request->get('sc_no'));      // �ƽи��ι��ֹ���֤���
            $menu->set_retGET('plan_no', $request->get('plan_no'));      // �ƽи��ι��ֹ���֤���
            $menu->set_retGETanchor('mark');    // �ޡ����إ����פ����� #��̵���������
            $result->add('aden_flg', '&aden_flg=1');
            $result->add('sc_no', '&sc_no=' . urlencode($request->get('sc_no')));
            $result->add('plan_no', '&plan_no=' . urlencode($request->get('plan_no')));
            $request->add('aden_key', $request->get('aden_flg'));
            $request->add('aden_plan', urlencode($request->get('plan_no')));
            $request->add('aden_sc', urlencode($request->get('sc_no')));
        }
        $request->add('aden_key', $request->get('aden_flg'));
        $request->add('aden_plan', urlencode($request->get('plan_no')));
        $request->add('aden_sc', urlencode($request->get('sc_no')));
    }
    
    /***** display()�� Private methods ���� *****/
    ///// �������ե������ɽ��
    private function CondFormExecute($menu, $request, $model, $uniq)
    {
        //////////// �߸˷���Ȳ񤫤�ƽФ���Ƥ��ʤ���Х��������򥻥å� 2007/02/08 ADD
        if (preg_match('/parts_stock_history_Main.php/', $menu->out_RetUrl()) && $request->get('noMenu') == '') {
            $menu->set_retGET('material', '1');
            $stockViewFlg = false;
        } else {
            $menu->set_action('�߸˷���Ȳ�',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
            $stockViewFlg = true;
        }
        
        require_once ('parts_stock_plan_ViewCondForm.php');
        return true;
    }
    
    /***** display()�� Private methods ���� *****/
    ///// ��ʬ�Υ�����ɥ���Ajaxɽ�����̥�����ɥ���ɽ��������
    private function ViewListExecute($menu, $request, $model, $uniq)
    {
        switch ($request->get('showMenu')) {
        case 'List':
            require_once ('parts_stock_plan_ViewListAjax.php');
            break;
        case 'ListWin':
        default:
            require_once ('parts_stock_plan_ViewListWin.php');
        }
        return true;
    }
    
} // class PartsStockPlan_Controller End

?>
