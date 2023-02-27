<?php
////////////////////////////////////////////////////////////////////////////////
// ����ֳ���ȿ���                                                           //
//                               Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/10/20 Created over_time_work_report_Main.php                          //
// 2021/11/01 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');     // zend 1.X ����ѥ� php4�θߴ��⡼��
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
//ob_start('ob_gzhandler');               // ���ϥХåե���gzip����

require_once ('../../function.php');    // TNK ������ function
require_once ('../../MenuHeader.php');  // TNK ������ menu class

//class Request
require_once ('../../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
require_once ('../../CalendarClass.php');              // �����������饹 �������塼��ǻ���
//require_once ('../../tnk_func.php');

//class over_time_work_report_Model
require_once ('over_time_work_report_Model.php');        // MVC �� Model��
//class over_time_work_report_Controller
require_once ('over_time_work_report_Controller.php');   // MVC �� Controller��

access_log();                           // Script Name �ϼ�ư����

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
    $menu->set_title('����ֳ���ȿ���');
    
    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');
    
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
    
    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    if( $session->get('User_ID') == '300667' ) {
//    if( $session->get('User_ID') == '300667' || $session->get('User_ID') == '300144') {
//    if(getCheckAuthority(69) ) { //  <!-- 69:��̳�ݰ��μҰ��ֹ�ʴ���������̳�ݡ�-->
        $request->add('debug', 'on');   // �ǥХå�ON ����꡼�����ϡ������Ȳ�
        if( ($login_uid = $request->get('login_uid')) != "" ) {
            $model = new over_time_work_report_Model($request, $login_uid);
//            $model->TEST();   // ���������Τ��Τ餻   �ǥ� ����꡼�����ϡ������Ȳ�
//            $model->TEST2();  // ���̤���ϤΤ��Τ餻 �ǥ� ����꡼�����ϡ������Ȳ�
        } else {
//            $model = new over_time_work_report_Model($request, '970392');   // �����ǽ
            $model = new over_time_work_report_Model($request, $session->get('User_ID'));
//            $model = new over_time_work_report_Model($request, '011061');   // ����Ĺ
//            $model = new over_time_work_report_Model($request, '017850');   // ������
//            $model = new over_time_work_report_Model($request, '007528');   // ISO(��̳)
//            $model = new over_time_work_report_Model($request, '300055');   // ��̳
//            $model = new over_time_work_report_Model($request, '300349');   // ���ʴ���
//            $model = new over_time_work_report_Model($request, '012980');   // ������
//            $model = new over_time_work_report_Model($request, '300098');   // �ʼ��ݾ�
//            $model = new over_time_work_report_Model($request, '300209');   // ����
//            $model = new over_time_work_report_Model($request, '012394');   // ������Ĺ�����������Ѵ���
//            $model = new over_time_work_report_Model($request, '010537');   // ��¤��
//            $model = new over_time_work_report_Model($request, '300233');   // ��¤��
//            $model = new over_time_work_report_Model($request, '016713');   // ������
//            $model = new over_time_work_report_Model($request, '300152');   // �������� �ײ衦����
//            $model = new over_time_work_report_Model($request, '016951');   // �������� ���
//            $model = new over_time_work_report_Model($request, '300331');   // ���ץ� ɸ��MA
//            $model = new over_time_work_report_Model($request, '300659');   // ���ץ� ɸ��HA
//            $model = new over_time_work_report_Model($request, '015989');   // ���ץ� ����
//            $model = new over_time_work_report_Model($request, '017728');   // ��˥�
        }
    } else {
        $model = new over_time_work_report_Model($request, $session->get('User_ID'));
    }
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new over_time_work_report_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
