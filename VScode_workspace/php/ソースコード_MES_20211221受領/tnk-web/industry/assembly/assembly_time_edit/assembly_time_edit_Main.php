<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�κ�ȴ������ӥǡ��� �Խ�  Client interface ��                       //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/12/07 Created   assembly_time_edit_Main.php                         //
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/09/20 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../function.php');                 // access_log()���ǻ���
require_once ('assembly_time_edit_Controller.php');     // MVC �� Controller��
require_once ('assembly_time_edit_Model.php');          // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 4);           // site_index=INDEX_INDUST(������˥塼) site_id=4(��Ω�����Խ�)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ��� (2005/10/21 Add)
    ///// ��˥塼������ showGroup �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    $session = new Session();
    $showGroup = $request->get('showGroup');
    if ($showGroup == '') {
        if ($session->get_local('showGroup') == '') {
            $showGroup = '0';               // ���꤬�ʤ�����̤������֤ˤ���
        } else {
            $showGroup = $session->get_local('showGroup');
        }
    }
    $session->add_local('showGroup', $showGroup);
    $request->add('showGroup', $showGroup);
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new AssemblyTimeEdit_Model($request);
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '��Ω ��� ���� �Ȳ��Խ� ��˥塼';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('��������ɽ',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new AssemblyTimeEdit_Controller($request, $model, $result, $session);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model, $session);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
