<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�����ײ�ɽ(AS/400��)�������塼�� �Ȳ�  Client interface ��           //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/23 Created   assembly_schedule_show_Main.php                     //
// 2006/03/03 out_action �˼���(��Ͽ)�����Ȳ���ɲ�                         //
// 2007/02/13 php-5.2.1��Memory limit is now enabled by default.�ˤʤä��Τ�//
//            memory_limit = '128M' ��ini_set()���ɲ�                       //
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('memory_limit', '128M');            // ����ȥ��㡼���Ѥ˻��ѥ��꡼�����䤹
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
require_once ('assembly_schedule_show_Controller.php'); // MVC �� Controller��
require_once ('assembly_schedule_show_Model.php');      // MVC �� Model��
require_once ('../../../../jpgraph.php');               // Common Graph class
require_once ('../../../../jpgraph_gantt.php');         // GanttChart Graph class
access_log();                               // Script Name �ϼ�ư����

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 7);           // site_index=INDEX_INDUST(������˥塼) site_id=7(����ɽ�Ȳ�)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '��Ω ���� �ײ�ɽ �Ȳ� ��˥塼';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    $menu->set_action('��������ɽ',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
    $menu->set_action('���ӹ����Ȳ�',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new AssemblyScheduleShow_Controller($menu);
    
    //////////// Client�ؽ��� [show()]
    $controller->display();
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
