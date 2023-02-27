<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ���ӥǡ��� �Ȳ�         MVC Controller ��           //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Controller.php                //
// 2006/01/20 Main�� showGroup showMenu�Υ����å��������Controller�ذ�ư   //
// 2007/03/26 Init()�᥽�åɤ��ɲä����������PageKeep�������ɲ�            //
//            �ײ��ֹ楯��å����ι��ֹ���¸�������ɲ�(�����ײ��ֹ���ѹ�)  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyProcessShow_Controller
{
    ///// Private properties
    private $model;                             // �ӥ��ͥ���ǥ����Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $session)
    {
        //////////// �ꥯ�����ȡ����å�������ν��������
        ///// ��˥塼������ showGroup��showMenu �Υǡ��������å� �� ���� (Model�ǻ��Ѥ���)
        $this->Init($request, $session);
        
        //////////// �ӥ��ͥ���ǥ����Υ��󥹥��󥹤��������ץ�ѥƥ�����Ͽ
        $this->model = new AssemblyProcessShow_Model($request);
        $session->add_local('viewPage', $this->model->get_viewPage());
        $session->add_local('pageRec' , $this->model->get_pageRec());
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $session)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('processShow');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        $rowsGroup = $this->model->getViewGroupList($result);
        $resGroup  = $result->get_array();
        switch ($request->get('showMenu')) {
        case 'StartList':                                   // ��Ω��� ����ɽ ɽ��
            $rows = $this->model->getViewStartList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewStartList.php');
            break;
        case 'StartTable':                                  // �嵭��Ajax�� ɽ��
            $rows = $this->model->getViewStartList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewStartTable.php');
            break;
        case 'EndList':                                     // ��Ω���� ����ɽ ɽ��
            $rows = $this->model->getViewEndList($result);
            $res  = $result->get_array();
            // $pageControl = $this->model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $this->model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $this->model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_process_show_ViewEndList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    protected function Init($request, $session)
    {
        ///// ��˥塼������ showMenu��showLine �Υǡ��������å� �� ����
        // showGroup�ν���
        $this->InitShowGroup($request, $session);
        // showMenu�ν���
        $this->InitShowMenu($request, $session);
        // PageKeep�ν���
        $this->InitPageKeep($request, $session);
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ////////// �ꥯ�����ȡ����å�������ν��������
    ///// ��˥塼������ showGroup��showMenu �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    // showGroup�ν���
    private function InitShowGroup($request, $session)
    {
        $showGroup = $request->get('showGroup');
        if ($showGroup == '') {
            if ($session->get_local('showGroup') == '') {
                $showGroup = '';                // ���꤬�ʤ��������ƤΥ饤�����ꤷ����Τȸ��ʤ�
            } else {
                $showGroup = $session->get_local('showGroup');
            }
        }
        if ($showGroup == '0') $showGroup = ''; // 0 �����Τ��̣���롣
        $session->add_local('showGroup', $showGroup);
        $request->add('showGroup', $showGroup);
    }
    
    ///// ��˥塼������ showGroup��showMenu �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    // showMenu�ν���
    private function InitShowMenu($request, $session)
    {
        $showMenu = $request->get('showMenu');
        if ($showMenu == '') {
            if ($session->get_local('showMenu') == '') {
                $showMenu = 'StartList';        // ���꤬�ʤ�����������
            } else {
                $showMenu = $session->get_local('showMenu');
            }
        }
        if ($showMenu != 'StartTable') {    // Ajax�ξ��ϥ��å�������¸���ʤ�
            $session->add_local('showMenu', $showMenu);
        }
        $request->add('showMenu', $showMenu);
    }
    
    ///// �ײ��ֹ�ǰ�����������ɽ��Ȳ񤷤���������ͤ�����å�
    // page_keep���������material_plan_no �ڤӥڡ�������ν���
    private function InitPageKeep($request, $session)
    {
        if ($request->get('page_keep') != '') {
            // ����å������ײ��ֹ�ιԤ˥ޡ�������
            if ($session->get('material_plan_no') != '') {
                $request->add('material_plan_no', $session->get('material_plan_no'));
            }
            // �ڡ��������� (�ƽФ������Υڡ������᤹)
            if ($session->get_local('viewPage') != '') {
                $request->add('CTM_viewPage', $session->get_local('viewPage'));
            }
            if ($session->get_local('pageRec') != '') {
                $request->add('CTM_pageRec', $session->get_local('pageRec'));
            }
        } else {
            $session->add_local('recNo', '-1'); // �����
        }
    }
    
} // class AssemblyProcessShow_Controller End

?>
