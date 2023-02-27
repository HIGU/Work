<?php
//////////////////////////////////////////////////////////////////////////////
// ��������������ߤ����(���ȥå�) �ޥ����� �Ȳ�����ƥʥ�             //
//              MVC Controller �� Main ��                                   //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/16 Created   equip_stopMaster_Main.php                           //
// 2005/07/28 ControllerHTTP_Class �� EquipControllerHTTP ���ѹ�            //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ����                   //
// 2005/11/01 $controller->display()�᥽�åɤΥ����Ȥ���                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '1');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../equip_function.php');             // �����ط����� function
require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../EquipControllerHTTP.php');        // �����ط��˳�ĥ���� MVC Controller Class
require_once ('equip_stopMaster_Controller.php');   // MVC �� Controller��
require_once ('equip_stopMaster_Model.php');        // MVC �� ��ǥ���
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 28);                    // site_index=40(������˥塼) site_id=28(��ߤ�����ޥ�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�

//////////// �ꥯ�����ȥ��֥������Ȥμ���
$request = new Request();
//////////// �ꥶ��ȤΥ��󥹥�������
$result = new Result();
//////////// ���å����Υ��󥹥�������
$session = new equipSession();

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("������������ �ޥ������ξȲ��Խ�&nbsp;&nbsp;{$session->getFactName()}");

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new EquipStopMaster_Model($session->get('factory'), $request);

//////////// ����ȥ��顼���Υ��󥹥�������
$controller = new EquipStopMaster_Controller($menu, $request, $result, $model, $session);

//////////// ���̽���
$controller->display($menu, $request, $result, $model, $session);

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
