<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ĺ�Ѳ�ĥ������塼��Ȳ�               Client interface ��           //
//                                              MVC Controller �� Main ��   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/03/11 Created   meeting_schedule_manager_Main.php                   //
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

require_once ('../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../function.php');                 // access_log()���ǻ���
require_once ('meeting_schedule_manager_Controller.php'); // MVC �� Controller��
require_once ('meeting_schedule_manager_Model.php');      // MVC �� Model��
require_once ('../../../jpgraph.php');               // Common Graph class
require_once ('../../../jpgraph_gantt_hour.php');         // GanttChart Graph class
access_log();                               // Script Name �ϼ�ư����

//// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(-1);                     // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����Ĺ�� ���(�ǹ礻)�Υ������塼��ɽ �Ȳ�');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    $session = new Session();
    
    // ǧ�ڤʤ�����Ͽ�Ѥߤξ��˥ꥯ�����Ȥǥ桼�������ѹ��Ǥ���
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') == '') {
            $_SESSION["s_sysmsg"] = "���ʤ��ϡ����Ĥ��줿�桼�����ǤϤ���ޤ���";
            header('Location: ' . H_WEB_HOST);
            exit();
        }
    }
    if ($session->get('User_ID') == '000000') {
        if ($request->get('calUid') != '') {
            $session->add('User_ID', $request->get('calUid'));
            $menu->set_auth_chk(-1);
        }
    }
    ///// �����Ȥ�ǯ���������ꤵ��Ƥ��뤫�����å�
    if ($request->get('year') == '' || $request->get('month') == '' || $request->get('day') == '') {
        // �����(����)������
        $request->add('year', date('Y')); $request->add('month', date('m')); $request->add('day', date('d'));
    }
    $yy_temp = str_pad($request->get('year'), 4, '0', STR_PAD_LEFT);
    $mm_temp = str_pad($request->get('month'), 2, '0', STR_PAD_LEFT);
    $dd_temp = str_pad($request->get('day'), 2, '0', STR_PAD_LEFT);
    $request->add('year', $yy_temp); $request->add('month', $mm_temp); $request->add('day', $dd_temp);
    ///// ����ɽ�����δ���(1����,7����,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');             // �����(�����Τ�)
        }
    }
    $session->add_local('listSpan', $request->get('listSpan')); // ���å����ǡ������ѹ�
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new MeetingSchedule_Model($request);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new MeetingSchedule_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}
main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
