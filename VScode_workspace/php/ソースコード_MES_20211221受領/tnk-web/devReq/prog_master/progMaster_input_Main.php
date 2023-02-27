<?php
//////////////////////////////////////////////////////////////////////////////
// �ץ����ޥ������Υ��ƥʥ�  Client interface ��                    //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_Main.php                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
require_once ('../../function.php');             // access_log()���ǻ���
require_once ('progMaster_input_Controller.php');         // MVC �� Controller��
require_once ('progMaster_input_Model.php');              // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    //$menu->set_site(INDEX_INDUST, 1);           // site_index=INDEX_INDUST(������˥塼) site_id=1(�����ƥ�ޥ������˳�����)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('�ץ����ޥ������Υ��ƥʥ�');
    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    ///// �����ե�����ɤΥꥯ�����ȼ���
    $pidKey = $request->get('pidKey');      // mipn(�����ֹ�)�Υ����ե������
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new ProgMaster_Model($request, $pidKey);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new ProgMaster_Controller($menu, $request, $result, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
