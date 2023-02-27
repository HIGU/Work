<?php
//////////////////////////////////////////////////////////////////////////////
// »��ͽ¬�ν��ס�ʬ�� ��� �Ȳ�(�Ȳ�Τ�)           Client interface ��   //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/19 Created   profit_loss_estimate_view_Main.php                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../function.php');                 // access_log()���ǻ���
require_once ('profit_loss_estimate_view_Controller.php');   // MVC �� Controller��
require_once ('profit_loss_estimate_view_Model.php');        // MVC �� Model��
access_log();                                           // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 17);          // site_index=INDEX_INDUST(������˥塼) site_id=17(�и˽���)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '»��ͽ¬�ν��ס�ʬ��';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new ProfitLossEstimate_Controller($menu);
    
    //////////// Client�ؽ��� [show()]
    $controller->display();
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
