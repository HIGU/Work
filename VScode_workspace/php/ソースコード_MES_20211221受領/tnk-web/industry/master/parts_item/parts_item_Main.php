<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ƥ�����ʡ����ʴط��Υ����ƥ�ޥ�����  Client interface ��      //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/13 Created   parts_item_Main.php                                 //
// 2005/09/15 site_id=1 �򤳤Υ����ƥ�ޥ������˳�����                      //
// 2005/09/26 main()������ɲ� �� display()�˥ѥ�᡼��(���֥�������)�ɲ�   //
// 2007/09/07 �ƽФ����Υڡ����ݻ�(̵���)���ɲ�                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
require_once ('../../../function.php');             // access_log()���ǻ���
require_once ('parts_item_Controller.php');         // MVC �� Controller��
require_once ('parts_item_Model.php');              // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 1);           // site_index=INDEX_INDUST(������˥塼) site_id=1(�����ƥ�ޥ������˳�����)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���ʡ����� �����ƥ�ޥ������ξȲ񡦥��ƥʥ�');
    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    ///// �����ե�����ɤΥꥯ�����ȼ���
    $partsKey = $request->get('partsKey');      // mipn(�����ֹ�)�Υ����ե������
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new PartsItem_Model($request, $partsKey);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new PartsItem_Controller($menu, $request, $result, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
