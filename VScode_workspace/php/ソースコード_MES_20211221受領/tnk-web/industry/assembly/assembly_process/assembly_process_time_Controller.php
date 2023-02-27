<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����κ�ȹ��� (��ꡦ��λ����) ������  MVC Controller ��            //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/30 Created   assembly_process_time_Controller.php                //
// 2007/06/17 display()�᥽�åɤ�apend���� if ($userEnd == '') ��           //
//            if ($userEnd == '' || $userRows <= 0) ����ȼԤΥ����å�    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyProcessTime_Controller
{
    ///// Private properties
    private $showMenu;                  // ��˥塼����
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($menu, $request, $result, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') $showMenu = 'StartList';       // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        
        ///// �����ե������ �ꥯ������ �ǡ�������
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $group_no   = $request->get('group_no');            // ���롼���ֹ�
        $user_id    = $request->get('user_id');             // �Ұ��ֹ�
        $plan_no    = $request->get('plan_no');             // �ײ��ֹ�
        $Ggroup_no  = $request->get('Ggroup_no');           // ��Ͽ���ѹ��ѥ��롼���ֹ�
        
        ///// ��Ͽ������������� �¹Իؼ��ꥯ�����Ȥ� �������ѿ�����Ͽ
        $apendUser  = $request->get('apendUser');           // ��Ω���Υ桼������Ͽ
        $apendPlan  = $request->get('apendPlan');           // ��Ω���ηײ��ֹ���Ͽ
        $deleteUser = $request->get('deleteUser');          // ��Ω���Υ桼�������
        $deletePlan = $request->get('deletePlan');          // ��Ω���ηײ��ֹ���
        $apendEnd   = $request->get('apendEnd');            // ��Ω�������Ͻ�λ
        $assyEnd    = $request->get('assyEnd');             // ��Ω��λ������ ���̴�λ(���Ǥ�ޤ�)
        $assyEndAll = $request->get('assyEndAll');          // ��Ω��λ������ ��細λ(���Ǥ�ޤ�)(2007/06/17���ߤϻ��Ѥ���Ƥ��ʤ�)
        $endCancel  = $request->get('endCancel');           // ��Ω��λ�μ��
        $groupEdit  = $request->get('groupEdit');           // ���롼�פ���Ͽ���ѹ�
        $groupOmit  = $request->get('groupOmit');           // ���롼�פκ��
        $groupActive= $request->get('groupActive');         // ���롼�פ�ͭ����̵��(�ȥ���)
        
        ///// ��Ͽ���Խ� �ǡ�����Ͽ
        $group_name = $request->get('group_name');          // ���롼��̾
        $div        = $request->get('div');                 // ������
        $product    = $request->get('product');             // ���ʥ��롼��
        $active     = $request->get('active');              // ���롼�פ�ͭ����̵��(�ȥ���)
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($apendUser != '') {                             // ��Ω���Υ桼������Ͽ (�ɲ�)
            $response = $model->userAdd($group_no, $user_id);
            if ($response) {
                $request->add('user_id', '');               // ��Ͽ�Ǥ����Τ�user_id��<input>�ǡ�����ä�
            }
        } elseif ($deleteUser != '') {                      // ��Ω���Υ桼������� (�������)
            $response = $model->userDelete($group_no, $user_id);
            if (!$response) $showMenu = 'apend';            // �������ʤ��ä��Τ���Ω�����Ͽ���̤ˤ���
        } elseif ($apendPlan != '') {                       // ��Ω���ηײ��ֹ���Ͽ
            $response = $model->planAdd($group_no, $plan_no);
            if ($response) {
                $request->add('plan_no', '');               // ��Ͽ�Ǥ����Τ�plan_no��<input>�ǡ�����ä�
            }
        } elseif ($deletePlan != '') {                      // ��Ω���ηײ��ֹ�(�桼������ʣ������) ���
            ///// ����ϸ��̺����serial_no����str_time,group_no,user_id��������������ǡ������繹����Ԥ� $plan_no�ϥ�å�������
            $response = $model->planDelete($serial_no, $plan_no);
            if (!$response) $showMenu = 'apend';            // �������ʤ��ä��Τ���Ω�����Ͽ���̤ˤ���
        } elseif ($apendEnd != '') {                        // ��Ω���κ�ȼԡ��ײ��ֹ�����Ͻ�λ����
            ///// �����ȤΥ��롼���ֹ�� work �ơ��֥�쥳���ɤ�������
            $response = $model->apendEnd($group_no);
            if (!$response) {
                $showMenu = 'apend';                        // �ѹ�����ʤ��ä��Τ���Ω�����Ͽ���̤ˤ���
            }
        } elseif ($assyEnd != '') {                         // ��Ω��λ������ (�ѹ�) ���̴�λ
            ///// serial_no�Ǹ���(��ȼԡ��ײ��ֹ�ñ��)�Ǵ�λ��Ԥ� $plan_no�ϥ�å�������
            $response = $model->assyEnd($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'apend';                        // �ѹ�����ʤ��ä��Τ���Ω�����Ͽ���̤ˤ���
            }
        } elseif ($assyEndAll != '') {                      // ��Ω��λ������ (�ѹ�) ��細λ
            ///// serial_no����str_time,group_no��������ư�細λ��Ԥ� $plan_no�ϥ�å�������
            $response = $model->assyEndAll($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'apend';                        // �ѹ�����ʤ��ä��Τ���Ω�����Ͽ���̤ˤ���
            }
        } elseif ($endCancel != '') {                       // ��Ω��λ�μ�� (�ѹ�)
            ///// serial_no����str_time,group_no,user_id��������ƺ�ȼ���ΰ���ä�Ԥ� $plan_no�ϥ�å�������
            $response = $model->endCancel($serial_no, $plan_no);
            if (!$response) {
                $showMenu = 'EndList';                      // �ѹ�����ʤ��ä��Τ���Ω��λ�������̤ˤ���
            }
        } elseif ($groupEdit != '') {                       // ���롼�פ���Ͽ���ѹ�
            if ($model->groupEdit($Ggroup_no, $group_name, $div, $product, $active)) {
                $request->add('Ggroup_no', '');             // ��Ͽ�Ǥ����Τ�group_no, group_name��<input>�ǡ�����ä�
                $request->add('group_name', '');
                $request->add('div', '');
                $request->add('product', '');
                $request->add('active', '');
            }
        } elseif ($groupOmit != '') {                       // ���롼�פκ��
            $response = $model->groupOmit($Ggroup_no, $group_name);
        } elseif ($groupActive != '') {                     // ���롼�פ�ͭ����̵��(�ȥ���)
            if ($model->groupActive($Ggroup_no, $group_name)) {
                $request->add('Ggroup_no', '');
                $request->add('group_name', '');
                $request->add('active', '');
            }
        }
        
        $this->showMenu = $showMenu;
        
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('process');
        
        ///// �����ե������ �ꥯ������ �ǡ�������
        $showMenu   = $this->showMenu;
        
        $serial_no  = $request->get('serial_no');           // tableϢ�� �����ե������
        $group_no   = $request->get('group_no');            // ���롼���ֹ�
        $user_id    = $request->get('user_id');             // �Ұ��ֹ�
        $plan_no    = $request->get('plan_no');             // �ײ��ֹ�
        $Ggroup_no  = $request->get('Ggroup_no');           // ��Ͽ���ѹ��ѥ��롼���ֹ�
        
        ///// ��Ͽ������������� �¹Իؼ��ꥯ�����Ȥ� �������ѿ�����Ͽ
        $groupCopy  = $request->get('groupCopy');           // ���롼�פ��ѹ��ե饰
        $userEnd    = $request->get('userEnd');             // ��ȼԤ��ɲý�λ�ܥ���
        
        ///// ��Ͽ���Խ� �ǡ�����Ͽ
        $group_name = $request->get('group_name');          // ���롼��̾
        $div        = $request->get('div');                 // ������
        $product    = $request->get('product');             // ���ʥ��롼��
        $active     = $request->get('active');              // ���롼�פ�ͭ����̵��(�ȥ���)
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {
        case 'StartList':                                   // ��Ω��� ����ɽ ɽ��
            $rows = $model->getViewStartList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'EndList':                                     // ��Ω��λ ����ɽ ɽ��
            $rows = $model->getViewEndList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'apend':                                       // ��Ω�������� (�ɲ�) ʣ���ηײ��б��Τ��� ApendList���������
            $userRows = $model->getViewUserListNotPage($group_no, $result);
            $userRes  = $result->get_array();
            $planRows = $model->getViewPlanListNotPage($result);
            $planRes  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        case 'group':                                       // ���롼�� ����ɽ ɽ��
            $rows = $model->getViewGroupList($result);
            $res  = $result->get_array();
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {
        case 'StartList':       // ��Ω��� ����ɽ ɽ��
            require_once ('assembly_process_time_ViewStartList.php');
            break;
        case 'EndList':         // ��Ω��λ ����ɽ ɽ��
            require_once ('assembly_process_time_ViewEndList.php');
            break;
        case 'apend':           // ��Ω�������� (�ɲ�)
            if ($userEnd == '' || $userRows <= 0) { // 2007/06/17 ����ȼ�0�ʤ�Ф��ɲ�(model���˸��̤˺�����å��ɲäΤ���)
                require_once ('assembly_process_time_ViewApendUser.php');
            } else {            // userEnd�ܥ��󤬲����줿��
                require_once ('assembly_process_time_ViewApendPlan.php');
            }
            break;
        case 'group':            // ���롼��(��ȶ�) ����ɽ ɽ��
            if ($groupCopy == 'go') {
                $focus    = 'group_name';
                $readonly = "readonly style='background-color:#d6d3ce;'";
            } else {
                $focus    = 'Ggroup_no';
                $readonly = '';
            }
            require_once ('assembly_process_time_ViewGroup.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤ���������ɽ��
            require_once ('assembly_process_time_ViewStartList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
