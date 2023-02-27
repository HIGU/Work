<?php
//////////////////////////////////////////////////////////////////////////////
// ���饹�ο���                                       Client interface ��   //
//   class template.                            MVC Controller �� Main ��   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/09/21 Created   class_template_Main.php                             //
//////////////////////////////////////////////////////////////////////////////
define('_TNK_DEBUG', false);                // �ǥХå�����true
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');                 // access_log()���ǻ���
access_log();                                           // Script Name �ϼ�ư����
// if (_TNK_DEBUG) access_log();                           // Script Name �ϼ�ư����

require_once ('../../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../../tnk_func.php');                 // workingDayOffset(-1), day_off(), date_offset() �ǻ���
require_once ('../../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('class_template_Controller.php');         // MVC �� Controller��
require_once ('class_template_Model.php');              // MVC �� Model��

///// Main���� main() function
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 29);          // site_index=INDEX_INDUST(������˥塼) site_id=29(������������)999(�����Ȥ򳫤�)
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $title = '�����Υ����ȥ�����';
    $menu->set_title($title);
    //////////// �ƽ����action̾�ȥ��ɥ쥹����
    // $menu->set_action('��������ɽ',   INDUST . 'material/allo_conf_parts_view.php');
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new ClassTemplate_Controller($menu);
    
    //////////// Client����Υꥯ�����Ƚ���
    $controller->Execute();
    //////////// Client�ؽ��� [show()]
    $controller->display();
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
