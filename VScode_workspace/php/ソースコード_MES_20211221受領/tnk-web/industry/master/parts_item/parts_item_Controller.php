<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ƥ�����ʡ����ʴط��Υ����ƥ�ޥ�����  MVC Controller ��        //
// Copyright (C) 2005-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Controller.php                           //
// 2005/09/16 ��������if ($preParts_no == '') $preParts_no = $parts_no;�ɲ� //
// 2005/09/17 $this->model->set_page_rec(20);�򥳥��� ComTableMnt���б�   //
// 2005/09/26 display()�˥ѥ�᡼��(���֥�������)�ɲ�                       //
// 2009/07/24 �����ֹ������ˡ������ä��Ȥ��������б�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class PartsItem_Controller
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
        if ($current_menu == '') $current_menu = 'list'; // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        
        ///// ɽ���ѥե������ �ǡ�������
        $parts_no   = $request->get('parts_no');        // mipn (�����ֹ�)
        $parts_name = $request->get('parts_name');      // midsc(̾��)
        $partsMate  = $request->get('partsMate');       // mzist(���)
        $partsParent= $request->get('partsParent');     // mepnt(�Ƶ���)
        $partsASReg = $request->get('partsASReg');      // madat(AS��Ͽ��)
        
        ////////// ��ǧ�ե�����Ǽ�ä������줿���Υꥯ�����ȼ���
        $cancel_apend  = $request->get('cancel_apend');
        $cancel_del    = $request->get('cancel_del');
        $cancel_edit   = $request->get('cancel_edit');
        
        /********* ������ *********/
        $preParts_no = $request->get('preParts_no');
        
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
        
        //////////////// ��Ͽ������������� POST �ѿ��� �������ѿ�����Ͽ
        $apend  = $request->get('apend');
        $edit   = $request->get('edit');
        $delete = $request->get('delete');
        
        ////////// MVC �� Model ���� �¹������å�����
        if ($apend != '') {         ////////// �ޥ������ɲ�
            $response = $model->table_add($parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
            if (!$response) $current_menu = 'apend';    // ��Ͽ����ʤ��ä��Τ��ɲò��̤ˤ���
        } elseif ($edit != '') {    ////////// �ޥ����� �ѹ�
            $response = $model->table_change($preParts_no, $parts_no, $parts_name, $partsMate, $partsParent, $partsASReg);
            if (!$response) {
                $current_menu = 'edit';                 // �ѹ�����ʤ��ä��Τ��Խ����̤ˤ���
                $cancel_edit  = '���';                 // �ѹ����Υǡ�����ɽ��
            }
        } elseif ($delete != '') {  ////////// �ޥ������������
            $response = $model->table_delete($parts_no);
            if (!$response) $current_menu = 'edit';     // �������ʤ��ä��Τ��Խ����̤ˤ���
        }
        
        $this->current_menu = $current_menu;
        
        ////////// �ꥯ�����ȥǡ����ΰ������ѹ������ΤǺ���Ͽ
        $request->add('cancel_apend', $cancel_apend);
        $request->add('cancel_del',   $cancel_del);
        $request->add('cancel_edit',  $cancel_edit);
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('item');
        
        ///// ������إ��֥������ȥ��ԡ�(HTML�������ѿ���)
        $current_menu = $this->current_menu;
        
        ///// ɽ���ѥե������ �ǡ�������
        $parts_no   = $request->get('parts_no');      // mipn (�����ֹ�)
        $parts_no   = str_replace('���㡼��', '#', $parts_no);
        $parts_name = $request->get('parts_name');    // midsc(̾��)
        $partsMate  = $request->get('partsMate');     // mzist(���)
        $partsParent= $request->get('partsParent');   // mepnt(�Ƶ���)
        $partsASReg = $request->get('partsASReg');    // madat(AS��Ͽ��)
        /********* ������ *********/
        $preParts_no = $request->get('preParts_no');  // �ѹ����������ֹ�
        
        ///// �����ե�����ɤΥꥯ�����ȼ���
        $partsKey   = $request->get('partsKey');      // mipn(�����ֹ�)�Υ����ե������
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($current_menu) {
        case 'list':            // �����ƥ� ����ɽ ɽ��
        case 'table':           // �����ƥ� ����ɽ �Υơ��֥����Τ�ɽ��(Ajax��)
            if ($partsKey == '') {
                // �����ե�����ɤ����ꤵ��Ƥ��ʤ�(���)�Τ����ϥե�����Τ�
                $rows = 0; $res = array();
            } else {
                $rows = $model->getViewDataList($result);
                $res  = $result->get_array();
            }
            break;
        case 'edit':            // �ޥ���������
        case 'confirm_delete':  // ����γ�ǧ
            if ($preParts_no == '') $preParts_no = $parts_no;   // �����ֹ椬���ꤵ��Ƥ��ʤ����Ͻ���Ƚ�ꤷ��parts_no����������
            if ($request->get('cancel_edit') == '') {     // ��ǧ�ե�����μ�äλ������Υǡ����򤽤Τޤ޻Ȥ�
                $rows = $model->getViewDataEdit($parts_no, $result);
                $parts_name = $result->get_once('parts_name');
                $partsMate  = $result->get_once('partsMate');
                $partsParent= $result->get_once('partsParent');
                $partsASReg = $result->get_once('partsASReg');
            }
            break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å�����������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($current_menu) {
        case 'list':            // ����ɽ ɽ��
            // $pageControll = $model->out_pageControll_HTML($menu->out_self()."?id={$uniq}");
            // $model->set_page_rec(20);     // 1�ǤΥ쥳���ɿ�
            $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self()."?id={$uniq}");
            require_once ('parts_item_ViewList.php');
            break;
        case 'table':           // �и� ����ɽ �Υơ��֥����Τ�ɽ��(Ajax��)
            require_once ('parts_item_ViewTable.php');
            break;
        case 'apend':           // �ޥ������ɲ�
            require_once ('parts_item_ViewApend.php');
            break;
        case 'edit':            // �ޥ���������
            require_once ('parts_item_ViewEdit.php');
            break;
        case 'confirm_apend':   // ��Ͽ�γ�ǧ
        case 'confirm_edit':    // �ѹ��γ�ǧ
        case 'confirm_delete':  // ����γ�ǧ
            require_once ('parts_item_ViewConfirm.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼
            require_once ('parts_item_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
}

?>
