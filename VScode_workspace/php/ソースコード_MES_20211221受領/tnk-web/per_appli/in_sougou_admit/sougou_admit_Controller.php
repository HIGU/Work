<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                                                         MVC Controller ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_Controller.php                             //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Sougou_Admit_Controller
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

        if ($request->get('sougou_update') == 'on') {
            $model->SougouUpdate($request);
        }

        $model->JyudenUpdate($request);

        $model->admitUpdate($request);
        $model->getEditData($request);
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('sougou_admit');

        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����

        $uid =  $model->getUid();

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        $send_uid = $request->get('send_uid');    // ������
        if( $send_uid ) {
            $model->AdmitRequestMaile($send_uid);
        }

        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {

        case 'List':                                        // �������塼�� ����ɽ�ǡ���
            if( $uid != '' ) {
                if( $model->getViewDataList($result) > 0 ) {
                    ;
                }
            }
            break;
        case 'Edit':                                        // �������塼�� ����ɽ�ǡ���
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            break;
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {

        case 'List':                                        // �������塼��ΰ��� ����
            require_once ('sougou_admit_ViewList.php');
            break;
        case 'Edit':                                        // �������塼��ΰ��� ����
            require_once ('sougou_admit_EditView.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('sougou_admit_ViewList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Admit_Controller

?>
