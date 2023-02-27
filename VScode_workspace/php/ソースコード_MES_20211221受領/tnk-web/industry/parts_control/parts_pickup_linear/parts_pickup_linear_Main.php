<?php
//////////////////////////////////////////////////////////////////////////////
// �����������ʽи� ��ꡦ��λ���� ������  Client interface ��            //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/12 Created   parts_pickup_linear_Main.php                        //
// 2005/09/28 ��������ɽ�Υ�󥯤�и�������ɽ���ɲ� ����å��ǥ�����   //
// 2005/12/08 current_menu �� ���å���󥪥֥������Ȥ���Ͽ                  //
// 2005/12/10 E_STRICT �ǥ��顼��å��������Фʤ��Τ� E_ALL ���ѹ�          //
//            ʸˡ���顼�� E_ALL �� �¹Ի��ξܺ٤� E_STRICT �ǹԤ���        //
// 2006/06/06 parts_pickup_time �� parts_pickup_linear ���ѹ�����˥��Ǻ��� //
//            ASP(JSP)�������ѻߤ��� php�ο侩�������ѹ�                    //
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
//////////////////////////////////////////////////////////////////////////////
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
require_once ('parts_pickup_linear_Controller.php');    // MVC �� Controller��
require_once ('parts_pickup_linear_Model.php');         // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 18);          // site_index=INDEX_INDUST(������˥塼) site_id=18(��˥�������ʽи˥�˥塼)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('��˥�������� ���� �и� ��˥塼');
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('��������ɽ',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ��� (2005/12/08 Add)
    ///// ��˥塼������ current_menu �Υǡ��������å� �� ���� (Model��Controller�ǻ��Ѥ���)
    $session = new Session();
    $current_menu = $request->get('current_menu');
    if ($current_menu == '') {
        if ($session->get_local('current_menu') == '') {
            $current_menu = 'list';         // ���꤬�ʤ����ϰ���ɽ��ɽ��(�ä˽��)
        } else {
            $current_menu = $session->get_local('current_menu');
        }
    }
    if ($current_menu == 'TimeEdit') {      // ���֤ν������̤ϥ��å�������¸���ʤ�
        $session->add_local('current_menu', 'EndList');
    } else {
        $session->add_local('current_menu', $current_menu);
    }
    $request->add('current_menu', $current_menu);
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new PartsPickupLinear_Model($request);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new PartsPickupLinear_Controller($menu, $request, $result, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
