<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//              MVC Controller ��                                           //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/04 Created   equip_groupMaster_Controller.php                    //
//            group �� group_no �� PostgreSQL�Ǥ�ͽ���ǻ��ѤǤ��ʤ�����   //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ���� View����������  //
//            �ѿ�̾�����󥹥��󥹤��б����Ƥ��ʤ�����__construct�����ƽ��� //
// 2005/09/18 �����ե������preGroup_no ��Edit¦�Ǥʤ�����������            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class EquipGroupMaster_Controller
{
    ///// Private properties
    // private $uniq;                      // �֥饦�����Υ���å����к���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5 �ܹԤ� __construct() ���ѹ�) (�ǥ��ȥ饯��__destruct())
    public function __construct($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('group');
        
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        ///// ɽ���ѥե������ �ǡ�������
        $group_no   = $request->get('group_no');
        $group_name = $request->get('group_name');
        $active     = $request->get('active');
        $regdate    = $request->get('regdate');
        $last_date  = $request->get('last_date');
        $last_user  = $request->get('last_user');
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $preGroup_no = $request->get('preGroup_no');
        
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
        
        ////////// MVC �� Model ���μ¹� ���å�����
        if ($apend != '') {         ////////// �ޥ������ɲ�
            $response = $model->table_add($group_no, $group_name, $active);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� �ѹ�
            $response = $model->table_change($preGroup_no, $group_no, $group_name, $active);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($group_no);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        ////////// MVC �� Model���� ���å����� & ��̼���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            $rows = $model->getViewDataList($result);
            $res = $result->get_array();
            break;
        case 'edit':            // �ޥ���������
        case 'confirm_delete':  // ����γ�ǧ
            if ($preGroup_no == '') $preGroup_no = $group_no;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��group_no����������
            if ($cancel_edit == '') {   // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($group_no, $result);
                $group_name = $result->get_once('group_name');
                $active     = $result->get_once('active');
                $regdate    = $result->get_once('regdate');
                $last_date  = $result->get_once('last_date');
            }
            break;
        }
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('equip_groupMaster_ViewList.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('equip_groupMaster_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('equip_groupMaster_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('equip_groupMaster_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('equip_groupMaster_ViewList.php');
        }
    }
    ///// MVC View���ν���
    public function display()
    {
        
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
