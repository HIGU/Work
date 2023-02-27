<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ����󥿡� �ޥ����� �Ȳ�����ƥʥ�                       //
//              MVC Controller ��                                           //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/15 Created   equip_counterMaster_Controller.php                  //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ���� View����������  //
//            �ѿ�̾�����󥹥��󥹤��б����Ƥ��ʤ�����__construct�����ƽ��� //
// 2005/09/18 �����ե������preMac_no preParts_no ��Edit¦�Ǥʤ�����������  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class EquipCounterMaster_Controller
{
    ///// Private properties
    // private $uniq;                      // �֥饦�����Υ���å����к���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5 �ܹԤ� __construct() ���ѹ�) (�ǥ��ȥ饯��__destruct())
    public function __construct($menu, $request, $result, $model, $session)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('counter');
        
        /////////// �����ʬ�ȹ���̾���������
        $factory   = $session->get('factory');
        $fact_name = $session->getFactName();
        
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        ///// ɽ���ѥե������ �ǡ�������
        $mac_no     = $request->get('mac_no');          // --|
        $parts_no   = $request->get('parts_no');        // --|--ʣ�祭��
        $count      = $request->get('count');
        $regdate    = $request->get('regdate');
        $last_date  = $request->get('last_date');
        $last_user  = $request->get('last_user');
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $preMac_no   = $request->get('preMac_no');
        $preParts_no = $request->get('preParts_no');
        
        //////////////// ��Ͽ������������� POST �ѿ��� �������ѿ�����Ͽ
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// ��ǧ�ե�������Ϥ��ǡ�������
        $confirm_apend  = $request->get('confirm_apend');
        $confirm_edit   = $request->get('confirm_edit');
        $confirm_delete = $request->get('confirm_delete');
        if ($confirm_apend != '') {
            $current_menu = 'confirm_apend';
        } elseif ($confirm_edit != '') {
            $current_menu = 'confirm_edit';
        } elseif ($confirm_delete != '') {
            $current_menu = 'confirm_delete';
        }
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υ��ơ��������������˥塼����
        if ($cancel_apend != '') {
            $current_menu = 'apend';
        } elseif ($cancel_edit != '') {
            $current_menu = 'edit';
        } elseif ($cancel_del != '') {
            $current_menu = 'edit';
        }
        
        ////////// MVC �� Model���μ¹ԥ��å�����
        if ($apend != '') {         ////////// �ޥ������ɲ�
            $response = $model->table_add($mac_no, $parts_no, $count);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� �ѹ�
            $response = $model->table_change($preMac_no, $preParts_no, $mac_no, $parts_no, $count);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($mac_no, $parts_no);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        ////////// MVC �� Model���Υ�˥塼���å����� & ��̼���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            $rows = $model->getViewDataList($result);
            $res = $result->get_array();
            break;
        case 'edit':            // �ޥ���������
            if ($preMac_no == '') $preMac_no = $mac_no;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��mac_no����������
            if ($preParts_no == '') $preParts_no = $parts_no;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��parts_no����������
            $mac_cnt = $model->getViewMac_noName($mac_no_name);   // ���깩��ε����ֹ�ȵ���̾����������
            // breakʸ���ʤ��������(edit�λ������嵭�Υ��å����ɲä��Ƥ���)
        case 'confirm_delete':  // ����γ�ǧ
            if ($cancel_edit == '') {   // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($mac_no, $parts_no, $result);
                $count      = $result->get_once('count');
                $regdate    = $result->get_once('regdate');
                $last_date  = $result->get_once('last_date');
            }
            break;
        case 'apend':
            $mac_cnt = $model->getViewMac_noName($mac_no_name);   // ���깩��ε����ֹ�ȵ���̾����������
            break;
        }
        ////////// ñ�Τε���̾�Τ�����(����)̾�μ���
        $mac_name   = $model->getViewMacName($mac_no);
        $parts_name = $model->getViewPartsName($parts_no);
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage');
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage', 'pageRec');
            require_once ('equip_counterMaster_ViewList.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('equip_counterMaster_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('equip_counterMaster_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('equip_counterMaster_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('equip_counterMaster_ViewList.php');
        }
    }
    ///// MVC View���ν���
    public function display()
    {
        
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
} // Class END

?>
