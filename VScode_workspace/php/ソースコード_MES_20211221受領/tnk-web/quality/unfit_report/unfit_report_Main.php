<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ ��Ŭ������ξȲ񡦥��ƥʥ�                                //
//                             Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/05/30 Created   unfit_report_Main.php                               //
// 2008/08/29 masterst���ܲ�ư����                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');       // ajax�ǻ��Ѥ�����
// ini_set('error_reporting', E_STRICT);           // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);                 // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
ob_start('ob_gzhandler');                          // ���ϥХåե���gzip����
session_start();                                   // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');                  // access_log()���ǻ���
require_once ('../../MenuHeader.php');                // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');      // TNK ������ MVC Controller Class
require_once ('../../CalendarClass.php');             // �����������饹 �������塼��ǻ���
require_once ('unfit_report_Controller.php');      // MVC �� Controller��
require_once ('unfit_report_Model.php');           // MVC �� Model��
access_log();                                      // Script Name �ϼ�ư����

//////////////// Main�� �� main()���
function main()
{
    //////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(-1);                    // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    //////////// ����������
    $menu->set_site(70, 71);                    // site_index=70(�ʼ�������˥塼) site_id=71(��Ŭ������ �Ȳ񡦺���)
    //////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('��Ŭ������ �Ȳ񡦺���');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    $session = new Session();
    
    //////////// ǧ�ڤʤ�����Ͽ�Ѥߤξ��˥ꥯ�����Ȥǥ桼�������ѹ��Ǥ���
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') != '') {
            $session->add('User_ID', $request->get('calUid'));
            $menu->set_auth_chk(-1);
        }
    }
    //////////// �����Ȥ�ǯ���������ꤵ��Ƥ��뤫�����å�
    if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
        //////// �����(����)������
        $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
    }
    //////////// ����ɽ�����δ���(1����,7����,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');                     // �����(�����Τ�)
        }
    }
    $session->add_local('listSpan', $request->get('listSpan')); // ���å����ǡ������ѹ�
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new UnfitReport_Model($request);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new UnfitReport_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                                                 // ���ϥХåե���gzip���� END
?>
