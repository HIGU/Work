<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                                         MVC Controller ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_Controller.php                               //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Monitoring_Controller
{
    ///// Private properties

    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� {php5 �ܹԤ� __construct() ���ѹ�} {�ǥ��ȥ饯��__destruct()}
    public function __construct($request, $model)
    {
        //////////// POST Data �ν����������
        ///// ��˥塼������ �ꥯ������ �ǡ�������
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('monitoring');
        
        ///// ��˥塼���� �ꥯ������ �ǡ�������
        
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        require_once ('monitoring_ViewMain.php');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Monitoring_Controller

?>
