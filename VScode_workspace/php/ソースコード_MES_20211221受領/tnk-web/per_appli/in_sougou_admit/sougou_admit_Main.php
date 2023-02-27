<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʾ�ǧ��                                                             //
//                               Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_admit_Main.php                                   //
// 2021/02/12 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X ����ѥ� php4�θߴ��⡼��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
//ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');    // TNK ������ function
require_once ('../../MenuHeader.php');  // TNK ������ menu class

//class Request
require_once ('../../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('../../CalendarClass.php');              // �����������饹 �������塼��ǻ���
//require_once ('../../tnk_func.php');

//class Sougou_Admit_Model
require_once ('sougou_admit_Model.php');        // MVC �� Model��
//class Sougou_Admit_Controller
require_once ('sougou_admit_Controller.php');   // MVC �� Controller��

access_log();                           // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(-1);                     // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����ϡʾ�ǧ��');

    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');

    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    if( $request->get('EditFlag') == 'on' ) {
        $menu->set_RetUrl(PER_APPLI . "in_sougou_admit/sougou_admit_Main.php"); // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    }
    
    //////////// ���å���� ���֥������Ȥμ���
    $session = new Session();

    // ǧ�ڤʤ�����Ͽ�Ѥߤξ��˥ꥯ�����Ȥǥ桼�������ѹ��Ǥ���
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') != '') {
            $session->add('User_ID', $request->get('calUid'));
            $menu->set_auth_chk(-1);
        }
    }
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
if( $request->get('c_agent') != '' ) {
    $model = new Sougou_Admit_Model($request, $request->get('c_agent'));
} else {
    if( $session->get('User_ID') == '300667' ) {
//        $model = new Sougou_Admit_Model($request, $session->get('User_ID'));
//        $model = new Sougou_Admit_Model($request, '011061');    // ����Ĺ
//        $model = new Sougou_Admit_Model($request, '017850');    // ������Ĺ
//        $model = new Sougou_Admit_Model($request, '300055');    // ��̳��Ĺ
        $model = new Sougou_Admit_Model($request, '300144');    // ��Ĺ����ë��
//        $model = new Sougou_Admit_Model($request, '017728');    // �ƥ�����(�Ұ��ֹ��ѹ���)

//        $model = new Sougou_Admit_Model($request, '012980');    // �ƥ�����(������Ĺ)
//        $model = new Sougou_Admit_Model($request, '012394');    // �ƥ�����(��¤��Ĺ)
//        $model = new Sougou_Admit_Model($request, '016713');    // �ƥ�����(������Ĺ)
    } else {
        $model = new Sougou_Admit_Model($request, $session->get('User_ID'));
    }
}
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new Sougou_Admit_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
