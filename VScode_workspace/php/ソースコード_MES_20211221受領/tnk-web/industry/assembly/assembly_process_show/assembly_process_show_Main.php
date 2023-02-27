<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ��� ��ꡦ��λ�ǡ��� �Ȳ�  Client interface ��                //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/19 Created   assembly_process_show_Main.php                      //
// 2006/01/20 showGroup showMenu�Υ����å��������Controller�ذ�ư          //
//            �嵭��ȼ�� Model��__construct()��showGroup�� ����=0 ���б�    //
// 2007/03/19 ʸ�������ɤ�����Τ���set_action('��������ɽ')��'AlloConfView'//
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/03/26 �ײ��ֹ楯��å����ι��ֹ���¸�������ɲ�                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../function.php');                 // access_log()���ǻ���
require_once ('assembly_process_show_Controller.php');  // MVC �� Controller��
require_once ('assembly_process_show_Model.php');       // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 6);           // site_index=INDEX_INDUST(������˥塼) site_id=6(��Ω�����Ȳ�)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    ///// ��˥塼������ showGroup��showMenu �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    $session = new Session();
    
    ///// ���ֹ���¸(�ײ��ֹ楯��å���)�Υꥯ�����Ƚ���
    if ( $request->get('recNo') ) {
        $session->add_local('recNo', $request->get('recNo'));
        exit();
    }
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '��Ω ��� ��ꡦ��λ �Ȳ� ��˥塼';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('AlloConfView',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('AlloConfView',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new AssemblyProcessShow_Controller($request, $session);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $session);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
