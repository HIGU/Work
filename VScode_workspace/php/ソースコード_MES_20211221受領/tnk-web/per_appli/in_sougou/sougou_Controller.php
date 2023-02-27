<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                                                         MVC Controller ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Controller.php                                   //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);       // E_STRICT=2048(php5) E_ALL=2047 debug ��

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Sougou_Controller
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
        $syainbangou = $request->get('syainbangou');               // �������塼�����Ͽ

        $request->add('syozoku', '---- �� ---- ��');
        $request->add('simei', '----- -----');
    }
    
    ///// MVC View���ν���
    public function display($menu, $request, $result, $model)
    {
        //////////// �֥饦�����Υ���å����к���
        $uniq = $menu->set_useNotCache('sougou');

        $sin_date = $request->get("sin_date");
        $str_date = $request->get("str_date");
        $end_date = $request->get("end_date");
        $str_time = $request->get("str_time");
        $end_time = $request->get("end_time");

        ///// ��˥塼���� �ꥯ������ �ǡ�������
        $showMenu   = $request->get('showMenu');            // �������åȥ�˥塼�����

        $syainbangou =  $request->get('syainbangou');

        $pageControll = $model->out_pageCtlOpt_HTML($menu->out_self());

        if( $syainbangou != '' ) {
            if( !$model->IsSyain() ) {
                $_SESSION['s_sysmsg'] .= $syainbangou . '���Ұ��ֹ�򤪳Τ��᲼������';
            } else {
                if( !$model->IsApproval() ) {
                    $_SESSION['s_sysmsg'] .= "����������(". $request->get('act_id') . ") ��ǧ ��ϩ ��Ͽ�ʤ��������Ԥ�Ϣ���Ʋ�������";
                }
                if( $model->getViewDataList($result) > 0 ) {
                    $res = array();
                    $res = $result->get_array();

                    $request->add('simei', $res[0][1]);
                    $request->add('syozoku', $res[0][2]);
                }
            }
        }

//if( $request->get('syain_no') == '300667' ) {
    if( $request->get('check_flag') == "ok" ) {
        $model->add($request);
        if( $request->get("reappl") ) {
            ?>
            <script>alert("����Ϥκƿ�������λ���ޤ�����"); window.open("about:blank","_self").close();</script>
            <?php
        }
        if( empty($_SESSION['s_sysmsg']) ) {
            $_SESSION['s_sysmsg'] .= "����Ϥο�������λ���ޤ�����";
        }
    }
/*
} else {
        $content   = $request->get('r1');
        if( $content != '' )
            $model->add($request);
}
*/
        ////////// MVC �� Model���� View�����Ϥ��ǡ�������
        switch ($showMenu) {

        case 'List':                                        // �������塼�� ����ɽ�ǡ���
        }
        
        ////////// HTML Header ����Ϥ��ƥ���å��������
        $menu->out_html_header();
        
        ////////// MVC �� View ���ν���
        switch ($showMenu) {

        case 'List':                                        // �������塼��ΰ��� ����
            require_once ('sougou_ViewList.php');
            break;
        case 'Check':                                       // ��ǧ ����
            require_once ('sougou_ViewCheck.php');
            break;
        case 'Re':                                          // �ƿ��� ����
            require_once ('sougou_ViewList.php');
            break;
        case 'Del':                                          // �ƿ��� ����
            require_once ('sougou_DelViewList.php');
            break;
        default:                // �ꥯ�����ȥǡ����˥��顼�ξ��Ͻ���ͤΰ�����ɽ��
            require_once ('sougou_ViewList.php');
            break;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/

    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/

} // End off Class Sougou_Controller

?>
