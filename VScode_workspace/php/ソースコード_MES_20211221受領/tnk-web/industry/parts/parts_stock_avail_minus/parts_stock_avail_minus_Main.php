<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȾȲ�  Client interface �� //
// �ǡ���������parts_stock_history_master_ftp_cli.php  MVC Controller Main��//
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/01 Created   parts_stock_avail_minus_Main.php                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

define('_TNK_DEBUG', false);                // �ǥХå�����true

require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../function.php');                 // access_log()���ǻ���
require_once ('parts_stock_avail_minus_Controller.php');// MVC �� Controller��
require_once ('parts_stock_avail_minus_Model.php');     // MVC �� Model��
access_log();                                           // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 18);                  // site_index=INDEX_INDUST(������˥塼) site_id=17(�ޥ��ʥ��ꥹ��)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);                   // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȾȲ�';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    $menu->set_action('�߸˷���',       INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
    $menu->set_action('�߸�ͽ��',       INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new PartsStockAvailMinus_Controller($menu);
    
    //////////// Client����Υꥯ�����Ƚ���
    $controller->Execute();
    //////////// Client�ؽ��� [show()]
    $controller->display();
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
