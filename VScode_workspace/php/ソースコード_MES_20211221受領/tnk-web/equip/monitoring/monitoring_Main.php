<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                               Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_Main.php                                     //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);                  // E_ALL='2047' debug ��
// ini_set('display_errors', '1');                     // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');        // zend 1.X ����ѥ� php4�θߴ��⡼��
session_start();                                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                           // ���ϥХåե���gzip����

require_once ('../../function.php');                // TNK ������ function
require_once ('../../MenuHeader.php');              // TNK ������ menu class

//class Request
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
//require_once ('../../tnk_func.php');

//class monitoring_Model
require_once ('monitoring_Model.php');              // MVC �� Model��
//class monitoring_Controller
require_once ('monitoring_Controller.php');         // MVC �� Controller��

access_log();                                       // Script Name �ϼ�ư����

///// Main�� �� main()���
function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                      // ǧ�ڥ����å� -1=ǧ�ڤʤ�, 0=���̰ʾ�
    
    ////////////// ����������
    // $menu->set_site(INDEX_INDUST, 1);            // ����������ʤ�
    ////////////// �꥿���󥢥ɥ쥹����
    // $menu->set_RetUrl(EQUIP_MENU);               // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���������� ��ư��');

    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');

    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    //$session = new Session();

    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new Monitoring_Model($request);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new Monitoring_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();     // ���ϥХåե���gzip���� END
?>
