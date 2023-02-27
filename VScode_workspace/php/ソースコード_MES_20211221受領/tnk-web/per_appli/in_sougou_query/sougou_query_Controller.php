<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                                                         MVC Controller ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_Controller.php                             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Sougou_Query_Controller
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
        if ($request->get('showMenu') == '') {
            $request->add('showMenu', 'List');              // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        }
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('sougou');

        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����

        if( $showMenu != 'List' ) {
            if( !$model->AmanoRun($result, $request) ) {
                $_SESSION['s_sysmsg'] .= '���Ͼ���ι����˼��Ԥ��ޤ�����';
            }

            if( !$model->CancelRun($result, $request) ) {
                $_SESSION['s_sysmsg'] .= '���ä������˼��Ԥ��ޤ�����';
            }

            if( $request->get('c2') != '') {
                if( $model->getHuzaisyaDataList($result, $request) < 0 )
                $_SESSION['s_sysmsg'] .= '�Ժ߼ԥꥹ�Ⱦ���������� ����!!';
            } else {
                if( $model->getViewDataList($result, $request) > 0 ) {
                    ;//$_SESSION['s_sysmsg'] .= '�����ҥå�';
                }
            }
        }
        
        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {

        case 'List':                                        // �������塼�� ����ɽ�ǡ���

        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {

        case 'List':                                        // �Ȳ��� ����
            require_once ('sougou_query_ViewList.php');
            break;
        case 'Results':                                     // �Ȳ��� ����
            require_once ('sougou_results_View.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('sougou_query_ViewList.php');
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Query_Controller

?>
