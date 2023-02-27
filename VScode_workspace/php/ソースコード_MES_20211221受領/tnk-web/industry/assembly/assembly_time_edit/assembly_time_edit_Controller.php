<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ������ӥǡ��� �Խ�  MVC Controller ��                         //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/07 Created   assembly_time_edit_Controller.php                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class AssemblyTimeEdit_Controller
{
    ///// Private properties
    private $rowsDupli;                     // Ʊ���ײ�θĿ�
    private $resDupli = array();            // Ʊ���ײ�Υ쥳��������
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model, $result, $session)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼�����ѥǡ�������
        if ($request->get('showMenu') == '') $request->add('showMenu', 'List'); // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($request->get('Apend') != '') {                 // ���ӥǡ������ɲ�
            if ($model->Apend($request)) {
                $request->add('user_id', '');               // ��Ͽ�Ǥ����Τ�user_id��<input>�ǡ�����ä�
                $request->add('plan_no', '');               // ��Ͽ�Ǥ����Τ�plan_no��<input>�ǡ�����ä�
                $request->add('str_time', '');              // ��Ͽ�Ǥ����Τ�str_time��<input>�ǡ�����ä�
                $request->add('end_time', '');              // ��Ͽ�Ǥ����Τ�end_time��<input>�ǡ�����ä�
                $request->add('showMenu', 'List');          // ��Ͽ�Ǥ����Τǰ������̤ˤ���
            }
        } elseif ($request->get('Delete') != '') {          // ���ӥǡ����κ�� (�������)
            if ($model->Delete($request)) {
                $request->add('showMenu', 'List');          // ������褿�Τǰ������̤ˤ���
            } else {
                $request->add('showMenu', 'ConfirmDelete'); // �������ʤ��ä��ΤǺ���γ�ǧ���̤��᤹
            }
        } elseif ($request->get('Edit') != '') {            // ���ӥǡ����ν���(�Խ�)
            if ($model->Edit($request, $session)) {
                $request->add('user_id', '');               // �ѹ��Ǥ����Τ�user_id��<input>�ǡ�����ä�
                $request->add('plan_no', '');               // �ѹ��Ǥ����Τ�plan_no��<input>�ǡ�����ä�
                $request->add('str_time', '');              // �ѹ��Ǥ����Τ�str_time��<input>�ǡ�����ä�
                $request->add('end_time', '');              // �ѹ��Ǥ����Τ�end_time��<input>�ǡ�����ä�
                $request->add('showMenu', 'List');          // �ѹ��Ǥ����Τǰ������̤ˤ���
            }
        } elseif ($request->get('ConfirmApend') != '') {    // �ɲ� ��ǧ�Ѥ˺Ʒ׻�����
            if (!$model->ConfirmApend($request, $result)) {
                // �Ʒ׻��ǥ��顼�Τ������ϥǡ����򤽤Τޤޤˤ����ɲò��̤��᤹
                $request->add('showMenu', 'ConfirmApendCancel');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        } elseif ($request->get('ConfirmDelete') != '') {   // ��� ��ǧ�Ѥ�Ʊ���ײ�ʬ�����
            if (!$model->ConfirmDelete($request, $result)) {
                // Ʊ���ײ�ʬ�μ����ǥ��顼�ΰ������̤��᤹
                $request->add('showMenu', 'List');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        } elseif ($request->get('ConfirmEdit') != '') {     // ���� ��ǧ�Ѥ˺Ʒ׻�����
            if (!$model->ConfirmEdit($request, $result, $session)) {
                // �Ʒ׻��ǥ��顼�Τ������ϥǡ����򤽤Τޤޤˤ��ƽ������̤��᤹
                $request->add('showMenu', 'ConfirmEditCancel');
            } else {
                $this->rowsDupli = $result->get('rows');
                $this->resDupli  = $result->get_array();
            }
        }
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model, $session)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('processEdit');
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� Model���� View�ѥǡ������� �� View�ν���
        $rowsGroup = $model->getViewGroupList($result);
        $resGroup  = $result->get_array();
        switch ($request->get('showMenu')) {
        case 'List':                                        // ��Ω���� ����ɽ ɽ��
            $rows = $model->getViewEndList($result);
            $res  = $result->get_array();
                // $pageControl = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            $pageControl = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_time_edit_ViewList.php');
            break;
        case 'Edit':                                        // ��Ω���� �ѹ��ǡ�������
            $rows = $model->getViewDataEdit($request->get('serial_no'), $request);
            // �ǡ����� $request->get() �Ǽ���
          case 'ConfirmEditCancel':       // ��ä������줿���ϥꥯ�����Ȥ򤽤Τޤ޻Ȥ�
            $rows = 1; // ���쥳���ɤ��ɲ�
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $model->ConfirmEditDupli($request, $result, $session);
            $rowsDupli = $result->get('rows');
            $resDupli  = $result->get_array();
            require_once ('assembly_time_edit_ViewEdit.php');
            break;
        case 'Apend':                                       // ��Ω���Ӥ��ɲ� (������)
            $request->add('str_year', date('Y')); $request->add('str_month', date('m')); $request->add('str_day', date('d'));
            $request->add('end_year', date('Y')); $request->add('end_month', date('m')); $request->add('end_day', date('d'));
            $request->add('assy_name', '&nbsp;'); $request->add('assy_no', '&nbsp;'); $request->add('plan', '&nbsp;'); $request->add('user_name', '&nbsp;');
          case 'ConfirmApendCancel':      // ��ä������줿���ϥꥯ�����Ȥ򤽤Τޤ޻Ȥ�
            $rows = 1; // ���쥳���ɤ��ɲ�
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            require_once ('assembly_time_edit_ViewApend.php');
            break;
        case 'ConfirmDelete':                               // ������γ�ǧ����
            $rows = $model->getViewDataEdit($request->get('serial_no'), $request);
            // �ǡ����� $request->get() �Ǽ���
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmDelete.php');
            break;
        case 'ConfirmEdit':                                 // �ѹ����γ�ǧ����
            $rows = 1; // ���쥳���ɤ��ɲ�
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmEdit.php');
            break;
        case 'ConfirmApend':                                // �ɲû��γ�ǧ����
            $rows = 1; // ���쥳���ɤ��ɲ�
            $pageParameter = $model->get_htmlGETparm() ."&id={$uniq}";
            $rowsDupli = $this->rowsDupli;
            $resDupli  = $this->resDupli;
            require_once ('assembly_time_edit_ViewConfirmApend.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
