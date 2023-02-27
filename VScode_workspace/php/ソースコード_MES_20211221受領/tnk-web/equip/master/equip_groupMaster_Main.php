<?php
//////////////////////////////////////////////////////////////////////////////
// �����������Υ��롼��(����)��ʬ �ޥ����� �Ȳ�����ƥʥ�               //
//              MVC Controller �� Main ��                                   //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/08/19 Created   equip_groupMaster_Main.php                          //
//            group �� group_no �� PostgreSQL�Ǥ�ͽ���ǻ��ѤǤ��ʤ�����   //
// 2005/08/18 �ڡ�������ǡ�����ComTableMntClass�ذܹԤ��ƥ��ץ��벽        //
// 2005/08/19 Controller��Class����Main Controller ����                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', '0');             // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../MenuHeader.php');              // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
require_once ('../equip_function.php');             // access_log(), getFactory(), equipAuthUser()���ǻ���
require_once ('equip_groupMaster_Controller.php');  // MVC �� Controller��
require_once ('equip_groupMaster_Model.php');       // MVC �� Model��
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 30);                    // site_index=40(������˥塼) site_id=30(���롼�׹����ʬ)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(EQUIP_MENU);              // �̾�ϻ��ꤹ��ɬ�פϤʤ�

/////////// �����ʬ�ȹ���̾���������
// $fact_name = getFactory($factoryList);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����ʬ �ޥ������ξȲ��Խ�');

//////////// �ꥯ�����ȥ��֥������Ȥμ���
$request = new Request();
//////////// �ꥶ��ȤΥ��󥹥�������
$result = new Result();

//////////// �ӥ��ͥ���ǥ����Υ��󥹥�������
$model = new EquipGroupMaster_Model($request);

//////////// ����ȥ��顼���Υ��󥹥�������
$controller = new EquipGroupMaster_Controller($menu, $request, $result, $model);

//////////// ���̽���
// $controller->display();

ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
