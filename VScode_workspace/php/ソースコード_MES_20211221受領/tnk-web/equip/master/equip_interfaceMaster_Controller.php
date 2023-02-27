<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��󥿡��ե������ޥ����� �Ȳ�����ƥʥ�                  //
//              MVC Controller ��                                           //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/13 Created   equip_interfaceMaster_Controller.php                //
// 2005/08/03 interface �� JavaScript ��ͽ���(NN7.1)�ʤΤ� inter ���ѹ�    //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ���� View����������  //
//            �ѿ�̾�����󥹥��󥹤��б����Ƥ��ʤ�����__construct�����ƽ��� //
// 2005/09/18 �����ե������preInterface ��Edit¦�Ǥʤ�����������           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class EquipInterfaceMaster_Controller
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
        $uniq = $menu->set_useNotCache('interface');
        
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        ///// ɽ���ѥե������ �ǡ�������
        $interface  = $request->get('inter');
        $host       = $request->get('host');
        $ip_address = $request->get('ip_address');
        $ftp_user   = $request->get('ftp_user');
        $ftp_pass   = $request->get('ftp_pass');
        $ftp_active = $request->get('ftp_active');
        $regdate    = $request->get('regdate');
        $last_date  = $request->get('last_date');
        $last_user  = $request->get('last_user');
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $preInterface = $request->get('preInterface');
        
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
            $response = $model->table_add($interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� �ѹ�
            $response = $model->table_change($preInterface, $interface, $host, $ip_address, $ftp_user, $ftp_pass, $ftp_active);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($interface);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        ////////// MVC �� Model���� ���å����� & ��̼���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            $rows = $model->getViewDataList($result);
            $res = $result->get_array();
            break;
        case 'edit':            // �ޥ���������
            if ($preInterface == '') $preInterface = $interface;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��interface����������
        case 'confirm_delete':  // ����γ�ǧ
            if ($cancel_edit == '') {   // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($interface, $result);
                $host       = $result->get_once('host');
                $ip_address = $result->get_once('ip_address');
                $ftp_user   = $result->get_once('ftp_user');
                $ftp_pass   = $result->get_once('ftp_pass');
                $ftp_active = $result->get_once('ftp_active');
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
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage');
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}", 'back', 'next', 'selectPage', 'prePage', 'pageRec');
            require_once ('equip_interfaceMaster_ViewList.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('equip_interfaceMaster_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('equip_interfaceMaster_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('equip_interfaceMaster_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('equip_interfaceMaster_ViewList.php');
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
