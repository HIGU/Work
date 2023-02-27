<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ҷ�ͭ �ǹ礻(���)�������塼��ɽ�ξȲ񡦥��ƥʥ�                  //
//                             Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2005-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/01 Created   meeting_schedule_Main.php                           //
// 2009/12/17 �Ȳ񡦰��������ɲäΰ�Ĵ��                               ��ë //
// 2010/03/11 ����Ĺ�ѥ������塼������Τ���ƥ����ѹ�                 ��ë //
// 2021/06/10 ����������ư�Ѥ�ǯ�������Ϥ�                         ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('mbstring.http_output', 'UTF-8');        // ajax�ǻ��Ѥ�����
// ini_set('error_reporting', E_STRICT);               // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);                  // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');                  // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X ����ѥ� php4�θߴ��⡼��
ob_start('ob_gzhandler');                           // ���ϥХåե���gzip����
session_start();                                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');                   // access_log()���ǻ���
require_once ('../MenuHeader.php');                 // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('../CalendarClass.php');              // �����������饹 �������塼��ǻ���
require_once ('meeting_schedule_Controller_k.php');   // MVC �� Controller��
require_once ('meeting_schedule_Model_k.php');        // MVC �� Model��
access_log();                                       // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(-1);                     // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���Ҷ�ͭ ���(�ǹ礻)�Υ������塼��ɽ �Ȳ��Խ�');
    
    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    $session = new Session();
    
    // ǧ�ڤʤ�����Ͽ�Ѥߤξ��˥ꥯ�����Ȥǥ桼�������ѹ��Ǥ���
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
    ///// ����������ư�Ѥ�ǯ����ꤵ��Ƥ��뤫�����å�
    if ($request->get('ind_ym') == 99) {
        // �����(����)������
        $request->add('ind_ym', date('Ym'));
        $_SESSION['ind_ym'] = $request->get('ind_ym');
    } elseif ($request->get('ind_ym') == '') {
        // �����(����)������
        $request->add('ind_ym', date('Ym'));
    } else {
        $_SESSION['ind_ym'] = $request->get('ind_ym');
    }
    ///// ����ɽ�����δ���(1����,7����,14,28...)
    if ($request->get('listSpan') == '') {
        if ($session->get_local('listSpan') != '') {
            $request->add('listSpan', $session->get_local('listSpan'));
        } else {
            $request->add('listSpan', '0');             // �����(�����Τ�)
        }
    }
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
