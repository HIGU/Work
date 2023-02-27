<?php
////////////////////////////////////////////////////////////////////////////////
// ����ϡʿ�����                                                             //
//                               Client interface  MVC Controller �� Main ��  //
// Copyright (C) 2020-2020 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2020/11/18 Created sougou_Main.php                                         //
// 2021/02/12 Release.                                                        //
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

//class Sougou_Model
require_once ('sougou_Model.php');        // MVC �� Model��
//class Sougou_Controller
require_once ('sougou_Controller.php');   // MVC �� Controller��

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
    $menu->set_title('����ϡʿ�����');

    //////////// �ƽФ����Υڡ�����ݻ�
    $menu->set_retGET('page_keep', 'on');

    //////////// �ꥯ�����ȥ��֥������Ȥμ���
    $request = new Request();
    
    //////////// �ꥶ��ȤΥ��󥹥�������
    $result = new Result();
    
    //////////// ���å���� ���֥������Ȥμ���
    //$session = new Session();
    
    ///// �����ե�����ɤΥꥯ�����ȼ���
    $syainbangou = $request->get('syainbangou');      // uid(�Ұ��ֹ�)�Υ����ե������

    //////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
    $model = new Sougou_Model($request, $syainbangou);
    
    //////////// ����ȥ��顼���Υ��󥹥�������
    $controller = new Sougou_Controller($request, $model);
    
    //////////// Client�ؽ���[show()]
    $controller->display($menu, $request, $result, $model);
}

main();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
