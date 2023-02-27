<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ���� �Ȳ� (�֣ͣ���)                     Client interface ��   //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created   parts_stock_history_Main.php (parts_stock_view.php) //
// 2007/03/09 ���ꥸ�ʥ��parts_stock_view.php ��parts_stock_plan_Main.php��//
//            ��碌�ƴ����ʣ֣ͣå�ǥ�ǥ����ǥ��󥰤�����                //
//            �ѹ������ backup/parts_stock_view.php �򻲾Ȥ��뤳�ȡ�       //
// 2007/03/20 parts_stock_view.php��¾�Υץ��������ˤ˻��Ѥ���Ƥ��뤿��//
//          parts_stock_history_Main.php��parts_stock_view.php�Ȥǣ��Ť�����//
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
require_once ('../../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../../function.php');                 // access_log()���ǻ���
require_once ('parts_stock_history_Controller.php');    // MVC �� Controller��
require_once ('parts_stock_history_Model.php');         // MVC �� Model��
access_log();                                           // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 40);          // site_index=INDEX_INDUST(������˥塼) site_id=40(�߸˷���)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '���� �߸� ���� �Ȳ�';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    $menu->set_action('��ݼ��ӾȲ�',     INDUST . 'payable/act_payable_view.php');
    // �ߤ��˸ƽФ����Ȥʤ�̵�¥롼�פ��򤱤뤿��
    // $controller->CondFormExecute()�᥽�åɤǰʲ��Υ��������򥻥åȤ��Ƥ���
    // $menu->set_action('�߸�ͽ��Ȳ�',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new PartsStockHistory_Controller($menu);
    
    //////////// Client�ؽ��� [show()]
    $controller->display();
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
