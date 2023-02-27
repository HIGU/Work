<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾȲ��                                                             //
//                               Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_query_Main.php                                   //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X ����ѥ� php4�θߴ��⡼��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');    // TNK ������ function
require_once ('../../MenuHeader.php');  // TNK ������ menu class

//class Request
require_once ('../../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('../../CalendarClass.php');              // �����������饹 �������塼��ǻ���
//require_once ('../../tnk_func.php');

//class Sougou_Query_Model
require_once ('sougou_query_Model.php');        // MVC �� Model��
//class Sougou_Query_Controller
require_once ('sougou_query_Controller.php');   // MVC �� Controller��

access_log();                           // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                     // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�

    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����ϡʾȲ��');

    //////////// ɽ�������
    $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');

    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');

    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();

    if( $request->get('cancel_run') == 'true' || $request->get('amano_run') == 'true' ) {
        $request->add('showMenu', 'Results');
    }

    if( $request->get('showMenu') == 'Results' ) {
        $menu->set_RetUrl(PER_APPLI . "in_sougou_query/sougou_query_Main.php"); // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    }

    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    

    //////////// ���å���� ���֥������Ȥμ���
    $session = new Session();

    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    if( $session->get('User_ID') == '300667' ) {
        // �ƥ��Ȥ���ˤϡ�63 65 �θ��¤򳰤���
//        $model = new Sougou_Query_Model($request, '016713');    //  nakayama
//        $model = new Sougou_Query_Model($request, '017507');    //  oyama
//        $model = new Sougou_Query_Model($request, '017728');    //  yasuda
        $model = new Sougou_Query_Model($request, $session->get('User_ID'));
//        $model = new Sougou_Query_Model($request, '300098');    //  usui
    } else {
        $model = new Sougou_Query_Model($request, $session->get('User_ID'));
    }
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new Sougou_Query_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
