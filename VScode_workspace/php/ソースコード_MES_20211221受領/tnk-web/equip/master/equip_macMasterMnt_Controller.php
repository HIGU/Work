<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ޥ����� �� �Ȳ� �� ���ƥʥ�                               //
//              MVC Controller ��                                           //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created   equip_macMasterMnt_Controller.php                   //
// 2002/08/08 register_globals = Off �б�                                   //
// 2003/06/17 servey(�ƻ�ե饰) Y/N ���ѹ��Ǥ��ʤ��Զ����� �ڤ�        //
//              �����ϥե������ץ�����󼰤��ѹ�                          //
// 2003/06/19 $uniq = uniqid('script')���ɲä��� JavaScript File��ɬ���ɤ�  //
// 2004/03/04 ���ǥơ��֥� equip_machine_master2 �ؤ��б�                   //
// 2004/07/12 Netmoni & FWS ���������� �����å����� ���Τ��� Net&FWS�����ɲ�//
//            CSV ������������ �ƻ������� ����̾�ѹ�                        //
// 2005/02/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/06/24 �ǥ��쥯�ȥ��ѹ� equip/ �� equip/master/                      //
// 2005/06/28 MVC��Controller�����ѹ�  equip_macMasterMnt_Controller.php    //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ���� View����������  //
//            �ѿ�̾�����󥹥��󥹤��б����Ƥ��ʤ�����__construct�����ƽ��� //
// 2005/09/18 �����ե������preMac_no ��Edit¦�Ǥʤ�����������              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class EquipMacMstMnt_Controller
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
        $uniq = $menu->set_useNotCache('machine');
        
        /////////// �����ʬ�ȹ���̾���������
        $factoryList = $session->get('factory');
        $fact_name   = $session->getFactName();
        
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        ///// ɽ���ѥե������ �ǡ�������
        $mac_no     = $request->get('mac_no');
        $mac_name   = $request->get('mac_name');
        $maker_name = $request->get('maker_name');
        $maker      = $request->get('maker');
        $factory    = $request->get('factory');
        $survey     = $request->get('survey');
        $csv_flg    = $request->get('csv_flg');
        $sagyouku   = $request->get('sagyouku');
        $denryoku   = $request->get('denryoku');
        $keisuu     = $request->get('keisuu');
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $pmac_no = $request->get('pmac_no');
        
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
            $response = $model->table_add($mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� ����
            $response = $model->table_change($pmac_no,$mac_no,$mac_name,$maker_name,$maker,$factory,$survey,$csv_flg,$sagyouku,$denryoku,$keisuu);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($mac_no);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        ////////// MVC �� Model���� ���å����� & ��̼���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            $rows = $model->getViewDataList($factoryList, $result);
            $res = $result->get_array();
            break;
        case 'edit':            // �ޥ���������
            if ($pmac_no == '') $pmac_no = $mac_no;     // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��mac_no����������
        case 'confirm_delete':  // ����γ�ǧ
            if ($cancel_edit == '') {   // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($mac_no, $result);
                $mac_name   = $result->get_once('mac_name');
                $maker_name = $result->get_once('maker_name');
                $maker      = $result->get_once('maker');
                $factory    = $result->get_once('factory');
                $survey     = $result->get_once('survey');
                $csv_flg    = $result->get_once('csv_flg');
                $sagyouku   = $result->get_once('sagyouku');
                $denryoku   = $result->get_once('denryoku');
                $keisuu     = $result->get_once('keisuu');
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
            require_once ('equip_macMasterMnt_ViewList.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('equip_macMasterMnt_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('equip_macMasterMnt_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('equip_macMasterMnt_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('equip_macMasterMnt_ViewList.php');
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
