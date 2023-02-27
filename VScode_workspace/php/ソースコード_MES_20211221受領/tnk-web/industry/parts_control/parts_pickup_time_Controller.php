<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  MVC Controller ��              //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_time_Controller.php                    //
// 2005/10/04 �и˺�ȼԤ���Ͽ�ơ��֥��ͭ����̵�����ɲ�  ȼ���᥽�å��ɲ�  //
// 2005/12/10 ��ꡦ��λ���֤ν����ѥ��å����ɲ�                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PartsPickupTime_Controller
{
    ///// Private properties
    private $current_menu;                  // ��˥塼����
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $current_menu = $request->get('current_menu');
        if ($current_menu == '') $current_menu = 'list';    // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        
        ///// �����ե������ & �ꥯ������ �ǡ�������
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $user_id    = $request->get('user_id');             // �Ұ��ֹ�
        $plan_no    = $request->get('plan_no');             // �ײ��ֹ�
        $user_name  = $request->get('user_name');           // ��̾(�и˺�ȼ���Ͽ���λ�̾)
        
        //////////////// ��Ͽ������������� POST �ѿ��� �������ѿ�����Ͽ
        $apend      = $request->get('apend');               // �и���������
        $delete     = $request->get('delete');              // �и����μ��
        $editEnd    = $request->get('editEnd');             // �и˴�λ������
        $editCancel = $request->get('editCancel');          // �и˴�λ�μ��
        $userEdit   = $request->get('userEdit');            // �и˺�ȼԤ���Ͽ���ѹ�
        $userOmit   = $request->get('userOmit');            // �и˺�ȼԤκ��
        $userActive = $request->get('userActive');          // �и˺�ȼԤ�ͭ����̵��(�ȥ���)
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($apend != '') {                                 // �и��������� (�ɲ�)
            $response = $model->table_add($plan_no, $user_id);
            if ($response) {
                $request->add('plan_no', '');               // ��Ͽ�Ǥ����Τ�plan_no��<input>�ǡ�����ä�
            } else {
                $current_menu = 'apend';                    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
            }
        } elseif ($delete != '') {                          // �и����μ�� (�������)
            $response = $model->table_delete($serial_no, $plan_no, $user_id);
            if (!$response) $current_menu = 'list';             // �������ʤ��ä��Τǽи����������̤ˤ���
        } elseif ($editEnd != '') {                         // �и˴�λ������ (�ѹ�)
            $response = $model->table_change('end', $serial_no, $user_id);
            if ($response) {
                // $current_menu = 'EndList';                      // �ѹ������Τǽи˴�λ�������̤ˤ���
                $request->add('plan_no', '');                   // ��λ���褿�Τ�plan_no��<input>�ǡ�����ä�
            } else {
                $current_menu = 'list';                         // �ѹ�����ʤ��ä��Τǽи����������̤ˤ���
            }
        } elseif ($editCancel != '') {                      // �и˴�λ�μ�� (�ѹ�)
            $response = $model->table_change('cancel', $serial_no, $user_id);
            if ($response) {
                // $current_menu = 'list';                         // �ѹ������Τǽи����������̤ˤ���
            } else {
                $current_menu = 'EndList';                      // �ѹ�����ʤ��ä��Τǽи˴�λ�������̤ˤ���
            }
        } elseif ($userEdit != '') {                        // �и˺�ȼԤ���Ͽ���ѹ�
            if ($user_name == '') {
                $request->add('user_name', $model->getUserName($user_id) );
            } else {
                if ($model->user_edit($user_id, $user_name)) {
                    // ��Ͽ�Ǥ����Τ�user_id, user_name��<input>�ǡ�����ä�
                    $request->add('user_id', '');
                    $request->add('user_name', '');
                }
            }
        } elseif ($userOmit != '') {                        // �и˺�ȼԤκ��
            $response = $model->user_omit($user_id, $user_name);
        } elseif ($userActive != '') {                      // �и˺�ȼԤ�ͭ����̵��(�ȥ���)
            if ($model->user_active($user_id, $user_name)) {
                $request->add('user_id', '');
                $request->add('user_name', '');
            }
        } elseif ($request->get('timeEdit') != '') {        // ��ꡦ��λ���֤ν���
            $response = $model->timeEdit($request);
            if ($response) {
                $current_menu = 'EndList';                  // �ѹ����褿�Τǽи˴�λ�������̤ˤ���
            } else {
                $current_menu = 'TimeEdit';                 // �ѹ�����ʤ��ä��Τǻ��ֽ������̤ˤ���
            }
        }
        
        $this->current_menu = $current_menu;
        
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('pickup');
        
        ///// �����ե������ & �ꥯ������ �ǡ�������
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $user_id    = $request->get('user_id');             // �Ұ��ֹ�
        $plan_no    = $request->get('plan_no');             // �ײ��ֹ�
        $user_name  = $request->get('user_name');           // ��̾(�и˺�ȼ���Ͽ���λ�̾)
        $userEdit   = $request->get('userEdit');            // �и˺�ȼԤ���Ͽ���ѹ�
        $userOmit   = $request->get('userOmit');            // �и˺�ȼԤκ��
        $userCopy   = $request->get('userCopy');            // �и˺�ȼԤ��ѹ��ե饰
        $userActive = $request->get('userActive');          // �и˺�ȼԤ�ͭ����̵��(�ȥ���)
        $current_menu = $this->current_menu;                // ���̥�˥塼������
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� Model���� View���ν���
        switch ($this->current_menu) {
        case 'EndList':                                     // �и˴�λ ����ɽ ɽ��
            $rows = $model->getViewDataEndList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParm = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('parts_pickup_time_ViewEndList.php');
            break;
        case 'apend':                                       // �и��������� (�ɲ�) ʣ���ηײ��б��Τ��� ApendList���������
            if ($user_id != '') {                           // user_id �����ꤵ��Ƥ��������List���������
                $rows = $model->getViewDataApendList($user_id, $result);
                $res  = $result->get_array();
            }
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $userRows = $model->getViewActiveUser($result);
            $userRes  = $result->get_array();
            if ($user_id == '') {
                require_once ('parts_pickup_time_ViewApendUserID.php');
            } else {
                require_once ('parts_pickup_time_ViewApend.php');
            }
            break;
        case 'user':                                        // �и� ��ȼ� ��Ͽ ����ɽ ɽ��
            $rows = $model->getViewUserList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            if ($userCopy == 'go') {
                $focus    = 'user_name';
                $readonly = "readonly style='background-color:#d6d3ce;'";
            } else {
                $focus    = 'user_id';
                $readonly = '';
            }
            require_once ('parts_pickup_time_ViewUser.php');
            break;
        case 'TimeEdit':                                    // ��ꡦ��λ�λ��� ���� ���� ɽ��
            if ($request->get('timeEdit') == '') {
                $rows = $model->getViewDataEdit($serial_no, &$result);
                $plan_no    = $result->get('plan_no');
                $assy_no    = $result->get('assy_no');
                $assy_name  = $result->get('assy_name');
                $plan_pcs   = $result->get('plan_pcs');
                $user_id    = $result->get('user_id');
                $user_name  = $result->get('user_name');
                $str_time   = $result->get('str_time');
                $end_time   = $result->get('end_time');
                $serial_no  = $result->get('serial_no');
                $pick_time  = $result->get('pick_time');
                // ������ʲ��Ͻ����ѥǡ���
                $str_year   = $result->get('str_year');
                $str_month  = $result->get('str_month');
                $str_day    = $result->get('str_day');
                $str_hour   = $result->get('str_hour');
                $str_minute = $result->get('str_minute');
                $end_year   = $result->get('end_year');
                $end_month  = $result->get('end_month');
                $end_day    = $result->get('end_day');
                $end_hour   = $result->get('end_hour');
                $end_minute = $result->get('end_minute');
            } else {    // ��Ͽ���顼�ξ��ϥꥯ�����ȥǡ�����ɽ������
                $rows = 1;  // rows�򥨥ߥ�졼��
                $plan_no    = $request->get('plan_no');
                $assy_no    = $request->get('assy_no');
                $assy_name  = $request->get('assy_name');
                $plan_pcs   = $request->get('plan_pcs');
                $user_id    = $request->get('user_id');
                $user_name  = $request->get('user_name');
                $str_time   = $request->get('str_time');
                $end_time   = $request->get('end_time');
                $serial_no  = $request->get('serial_no');
                $pick_time  = $request->get('pick_time');
                // ������ʲ��Ͻ����ѥǡ���
                $str_year   = $request->get('str_year');
                $str_month  = $request->get('str_month');
                $str_day    = $request->get('str_day');
                $str_hour   = $request->get('str_hour');
                $str_minute = $request->get('str_minute');
                $end_year   = $request->get('end_year');
                $end_month  = $request->get('end_month');
                $end_day    = $request->get('end_day');
                $end_hour   = $request->get('end_hour');
                $end_minute = $request->get('end_minute');
            }
            $pageParm = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('parts_pickup_time_TimeEdit.php');
            break;
        case 'list':                                        // �и���� ����ɽ ɽ��
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤ���������ɽ��
            $rows = $model->getViewDataList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('parts_pickup_time_ViewList.php');
            break;
        }
        
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
